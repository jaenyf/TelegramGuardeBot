<?php

namespace TelegramGuardeBot;

use ErrorException;
use TelegramGuardeBot\TelegramApi;
use TelegramGuardeBot\i18n\GuardeBotMessagesBase;
use TelegramGuardeBot\Validators\MlSpamTextValidator;
use TelegramGuardeBot\Learners\MlSpamTextLearner;
use TelegramGuardeBot\Learners\MlHamTextLearner;
use TelegramGuardeBot\Managers\Masters\MastersManager;
use TelegramGuardeBot\Managers\Spams\SpammersManager;
use TelegramGuardeBot\Helpers\ArrayHelper;
use TelegramGuardeBot\Helpers\TelegramHelper;
use TelegramGuardeBot\Actions\MessageActionProcessor;
use TelegramGuardeBot\UpdateHandlers\CallbackQueryUpdateHandler;
use TelegramGuardeBot\UpdateHandlers\NewMemberUpdateHandler;
use TelegramGuardeBot\UpdateHandlers\ChatJoinRequestUpdateHandler;

/**
 * GuardeBot Class.
 *
 * @author jaenyf
 */
class GuardeBot
{
    /**
     * Constant for the webhook lock file name.
     */
    const WEBHOOK_LOCK_FILENAME = 'guardebot.lock';

    private TelegramApi $telegram;
    private $webHookUniqueName = null;

    /**
     * Create a GuardeBot instance
     * \param $bot_token the bot token
     * \param $log_errors enable or disable the logging
     * \param $proxy array with the proxy configuration (url, port, type, auth)
     * \return an instance of the class.
     */
    public function __construct(
        TelegramApi $telegramApi,
        string $webHookUniqueName
    ) {
        $this->webHookUniqueName = trim(isset($webHookUniqueName) ? $webHookUniqueName : '');
        if ($this->webHookUniqueName === '') {
            throw new \Exception('WebHook unique name has to be defined');
        }
        $this->telegram = $telegramApi;
    }

    private function deriveWebHookUniqueFilename($baseFilename)
    {
        $baseFilenameExt = pathinfo($baseFilename, PATHINFO_EXTENSION);
        return basename($baseFilename, '.' . $baseFilenameExt) . '-' . $this->webHookUniqueName . '.' . $baseFilenameExt;
    }

    /**
     * returns a 2-elements array with the update id and the update date
     */
    private function getLastHandledUpdateInfo()
    {
        $hookFileName = $this->deriveWebHookUniqueFilename(self::WEBHOOK_LOCK_FILENAME);
        $file = fopen($hookFileName, "r");
        $result = fgetcsv($file);
        if ($result === false) {
            $result = ['0', '0'];
        }
        fclose($file);
        return $result;
    }

    /**
     * returns a 2-elements array with the update id and the update date
     */
    private function setLastHandledUpdateInfo($updateId, $updateDate)
    {
        $hookFileName = $this->deriveWebHookUniqueFilename(self::WEBHOOK_LOCK_FILENAME);
        $file = fopen($hookFileName, "w");
        fputcsv($file, [$updateId, $updateDate]);
        fclose($file);
    }

    public function approveMember(int $chatId, int $userId)
    {
        $chatMember = $this->telegram->getChatMember([
            'chat_id' => $chatId,
            'user_id' => $userId
        ]);

        if ($chatMember->status == 'creator' || $chatMember->status == 'administrator') {
            $this->log($chatMember, 'Ignoring member approval for administrators...');
            return;
        }

        $this->telegram->restrictChatMember([
            'chat_id' => $chatId,
            'user_id' => $userId,
            'permissions' => [
                'can_send_messages' => true,
                'can_send_media_messages' => true,
                'can_send_polls' => true,
                'can_send_other_messages' => true,
                'can_add_web_page_previews' => true,
                'can_change_info' => true,
                'can_invite_users' => true,
                'can_pin_messages' => true
            ]
        ]);
    }

    public function ejectMember(int $chatId, int $userId)
    {
        $chatMember = $this->telegram->getChatMember([
            'chat_id' => $chatId,
            'user_id' => $userId
        ]);

        if ($chatMember->status == 'creator') {
            $this->log($chatMember, 'Ignoring member ejection for creators...');
            return;
        }

        $this->telegram->banChatMember(['chat_id' => $chatId, 'user_id' => $userId, 'until_date' => time() + 30, 'revoke_messages' => false]);
    }

    public function deleteMessage(int $chatId, int $messageId)
    {
        $this->telegram->deleteMessage(['chat_id' => $chatId, 'message_id' => $messageId]);
    }

    public function isHooked()
    {
        $hookInfo = ArrayHelper::toObject($this->telegram->getWebhookInfo());
        return !empty($hookInfo->url);
    }

    /**
     * \param $allowedUpdate Array, Use default null value to use the default TelegramApi behavior, use empty array to explicitely allow all updates
     */
    public function hook($url, $certificate = '', $allowedUpdate = null, $dropPendingUpdates = false)
    {
        if (empty($url)) {
            throw new ErrorException('Can not hook to an empty url');
        }

        if (!$this->isHooked()) {

            if (empty($allowedUpdate)) {
                $allowedUpdate = [
                    'message',
                    'edited_message',
                    'channel_post',
                    'edited_channel_post',
                    'inline_query',
                    'chosen_inline_result',
                    'callback_query',
                    'shipping_query',
                    'pre_checkout_query',
                    'poll',
                    'poll_answer',
                    'my_chat_member',
                    'chat_member',
                    'chat_join_request'
                ];
            }

            if (!$this->telegram->setWebhook($url, $certificate, null, null, $allowedUpdate, $dropPendingUpdates)) {
                throw new \Exception('Failed to set web hook');
            }

            App::getInstance()->getScheduler()->start();

            //create the hook lock file as all went well:
            $hookFileName = $this->deriveWebHookUniqueFilename(self::WEBHOOK_LOCK_FILENAME);
            fclose(fopen($hookFileName, "w+"));

            $this->log('web hook set');
        } else {
            $this->log('web hook already set');
        }
    }

    public function unHook($dropPendingUpdates = false)
    {
        if ($this->isHooked()) {
            $this->telegram->deleteWebhook($dropPendingUpdates);
        }

        App::getInstance()->getScheduler()->stop();

        $hookFileName = $this->deriveWebHookUniqueFilename(self::WEBHOOK_LOCK_FILENAME);
        if (file_exists($hookFileName)) {
            $hookFilePointer = fopen($hookFileName, 'w+');
            fclose($hookFilePointer);
            unlink($hookFileName);
        }
        $this->log('web hook deleted');
    }

    /**
     * @deprecated
     * Log (debug) to file and telegram test group
     */
    public function log($element, $title = null)
    {
        App::getInstance()->getLogger()->debug($title, [$element]);
    }

     /**
     * @deprecated
     * Log (info) to file and telegram test group
     */
    private function logInfo($element, $title = null)
    {
        App::getInstance()->getLogger()->info($title, [$element]);
    }

    public function say($chatId, $message)
    {
        $this->telegram->sendMessage(
            array(
                'chat_id' => $chatId,
                'text' => $message
            )
        );
    }

    public function replyTo($chatId, $messageId, $message)
    {
        $this->telegram->sendMessage(
            array(
                'chat_id' => $chatId,
                'reply_to_message_id' => $messageId,
                'text' => $message
            )
        );
    }


    /**
     * Handle the updates received by the web hook
     * \param $updates the updates in json format
     */
    public function handleUpdate($update)
    {
        $updateId = $update->update_id;
        $updateInfo = $this->getLastHandledUpdateInfo();
        if ($updateId == $updateInfo[0]) {
            //this update has already been processed
            return;
        }

        (new MessageActionProcessor())->process(App::getInstance()->getDIContainer()->get('appConfig')->messagesActions, $update);

        $this->processUpdate($update);
        $this->setLastHandledUpdateInfo($updateId, time());

        return true;
    }

    /**
     * Handle update message and act based upon it
     * \param $update the telegram update
     */
    private function processUpdate($update)
    {
        $message = (isset($update->message) && isset($update->message->text)) ? $update->message->text : '';

        $commandText = '';
        $newMember = null;
        $callbackQuery = null;
        if ($this->isCommand($message, $commandText)) {
            $this->log($commandText, 'Processing command ...');
            $this->processCommand($commandText, $update);
        } else if (TelegramHelper::isNewMemberIncoming($update, $newMember)) {
            (new NewMemberUpdateHandler($this->telegram))->handle($update, $newMember);
        } else if (TelegramHelper::isChatJoinRequest($update, $newMember)) {
            (new ChatJoinRequestUpdateHandler($this->telegram))->handle($update, $newMember);
        } else if (TelegramHelper::isCallbackQuery($update, $callbackQuery)) {
            (new CallbackQueryUpdateHandler($this->telegram))->handle($update, $callbackQuery);
        } else {
            $this->log($update, 'Unkown process update type !');
            $spamValidator = new MlSpamTextValidator();
            $isValid = $spamValidator->validate($message);
            if (!$isValid) {
                //this is probably a spam
                //TODO: handle spam
                $this->log($message, 'Looks like spam ...');
                $this->replyTo($update->message->chat->id, $update->message->message_id, '@admin ('. GuardeBotMessagesBase::getInstance()->get(GuardeBotMessagesBase::MESSAGE_LOOKS_LIKE_SPAM) .')');
            }
        }
    }

    /**
     * Whether or not the given text is a command or a friendly command
     * \param $text the text to analyse
     * \param $commandText the extracted command text
     */
    private function isCommand($text, &$commandText): bool
    {
        $commandHeader = GuardeBotMessagesBase::getInstance()->get(GuardeBotMessagesBase::CMD_HEADER);
        $matches = [];
        if (preg_match(
            '/^(?i)(' . preg_quote($commandHeader, '/') . ')([\w]+)\s*/',
            $text,
            $matches
        ))
        {
            $commandText = rtrim($matches[2]);
            return true;
        }
        else
        {
            $friendlyCommandHeader = GuardeBotMessagesBase::getInstance()->get(GuardeBotMessagesBase::FCMD_HEADER);
            $matches = [];
            if (preg_match(
                '/^(?i)(' . preg_quote($friendlyCommandHeader) . ')\s*[!]*\s*[,;-]{0,1}\s*([\w\s]+)\s*[!]*\s*/',
                $text,
                $matches
            ))
            {
                $commandText = rtrim($matches[2]);
                return true;
            }
        }

        return false;
    }

    private function processCommand($commandText, $update)
    {
        $loweredCommandText = strtolower($commandText);
        switch ($loweredCommandText)
        {
            case GuardeBotMessagesBase::getLowered(GuardeBotMessagesBase::CMD_MARK_AS_HAM):
            case GuardeBotMessagesBase::getLowered(GuardeBotMessagesBase::FCMD_MARK_AS_HAM):
                //the behavior here is to considere this command is in a reply of the message to mark as spam
                $commandAuthor = null;
                if (TelegramHelper::tryGetMemberInfoFromStructure($update->message->from, $commandAuthor))
                {
                    //Only Masters can execute this command
                    if (MastersManager::getInstance()->has($commandAuthor->userId))
                    {
                        $replyToMessageText = '';
                        if (TelegramHelper::tryGetReplyToMessageText($update, $replyToMessageText))
                        {
                            $learner = new MlHamTextLearner();
                            $learner->learn($replyToMessageText);

                            //delete command update message
                            $this->telegram->deleteMessage(["message_id" => $update->message->message_id, "chat_id" => $update->message->chat->id], false);

                            $this->log($replyToMessageText, 'marked as ham !');
                        }
                    }
                }
                break;

            case GuardeBotMessagesBase::getLowered(GuardeBotMessagesBase::CMD_MARK_AS_SPAM):
            case GuardeBotMessagesBase::getLowered(GuardeBotMessagesBase::FCMD_MARK_AS_SPAM):
                //the behavior here is to considere this command is in a reply of the message to mark as spam
                $commandAuthor = null;
                if (TelegramHelper::tryGetMemberInfoFromStructure($update->message->from, $commandAuthor))
                {
                    //Only Masters can execute this command
                    if (MastersManager::getInstance()->has($commandAuthor->userId))
                    {
                        $replyToMessageText = '';
                        if (TelegramHelper::tryGetReplyToMessageText($update, $replyToMessageText))
                        {
                            $learner = new MlSpamTextLearner();
                            $learner->learn($replyToMessageText);

                            //delete command update message
                            $this->telegram->deleteMessage(["message_id" => $update->message->message_id, "chat_id" => $update->message->chat->id], false);
                            //delete spam message
                            $this->telegram->deleteMessage(["message_id" => $update->message->reply_to_message->message_id, "chat_id" => $update->message->reply_to_message->chat->id], false);

                            $spammer = null;
                            if (TelegramHelper::tryGetMemberInfoFromStructure($update->message->reply_to_message->from, $spammer))
                            {
                                if (!MastersManager::getInstance()->has($spammer->userId))
                                {
                                    SpammersManager::getInstance()->add($spammer->userId, $spammer->userName, $spammer->firstName, $spammer->lastName);
                                }
                                $messageChatId = null;
                                if (TelegramHelper::tryGetMessageChatId($update, $messageChatId))
                                {
                                    if (TelegramHelper::banChatMember($this->telegram, $messageChatId, $spammer))
                                    {
                                        $spammerDisplayName = TelegramHelper::getBestMessageAuthorDisplayName($spammer);
                                        $this->say($messageChatId, GuardeBotMessagesBase::get(GuardeBotMessagesBase::ACK_BAN_MESSAGE_AUTHOR, [$spammerDisplayName]));
                                    }
                                }
                            }
                            $this->log($replyToMessageText, 'marked as spam !');
                        }
                    }
                }
                break;
            case GuardeBotMessagesBase::getLowered(GuardeBotMessagesBase::CMD_BAN_MESSAGE_AUTHOR):
            case GuardeBotMessagesBase::getLowered(GuardeBotMessagesBase::FCMD_BAN_MESSAGE_AUTHOR):
                //the behavior here is to considere this command is in a reply of the message to mark the author as spammer
                $messageAuthorInfo = null;
                if (TelegramHelper::tryGetMessageAuthorInfo($update, $messageAuthorInfo))
                {
                    if (!MastersManager::getInstance()->has($messageAuthorInfo->userId))
                    {
                        SpammersManager::getInstance()->add($messageAuthorInfo->userId, $messageAuthorInfo->userName, $messageAuthorInfo->firstName, $messageAuthorInfo->lastName);
                        $messageChatId = null;
                        if (TelegramHelper::tryGetMessageChatId($update, $messageChatId))
                        {
                            if (TelegramHelper::banChatMember($this->telegram, $messageChatId, $messageAuthorInfo))
                            {
                                $authorDisplayName = TelegramHelper::getBestMessageAuthorDisplayName($messageAuthorInfo);
                                $this->say($messageChatId, GuardeBotMessagesBase::get(GuardeBotMessagesBase::ACK_BAN_MESSAGE_AUTHOR, [$authorDisplayName]));
                            }
                        }
                    }
                }
                break;
            default:
                $this->log('unrecognized command : \'' . $commandText . '\'');
                break;
        }
    }
}
