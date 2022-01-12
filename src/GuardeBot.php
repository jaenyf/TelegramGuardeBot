<?php

namespace TelegramGuardeBot;

use ErrorException;
use TelegramGuardeBot\TelegramApi;
use TelegramGuardeBot\i18n\GuardeBotMessagesBase;
use TelegramGuardeBot\Log\GuardeBotLogger;
use TelegramGuardeBot\Validators\MlSpamTextValidator;
use TelegramGuardeBot\Learners\MlSpamTextLearner;
use TelegramGuardeBot\Managers\Masters\MastersManager;
use TelegramGuardeBot\Managers\Spams\SpammersManager;
use TelegramGuardeBot\Helpers\ArrayHelper;



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
    private int $logChatId;

    /**
     * Create a GuardeBot instance
     * \param $bot_token the bot token
     * \param $log_errors enable or disable the logging
     * \param $proxy array with the proxy configuration (url, port, type, auth)
     * \return an instance of the class.
     */
    public function __construct(
        TelegramApi $telegramApi,
        string $webHookUniqueName,
        int $logChatId
    ) {
        $this->webHookUniqueName = trim(isset($webHookUniqueName) ? $webHookUniqueName : '');
        if ($this->webHookUniqueName === '') {
            throw new \Exception('WebHook unique name has to be defined');
        }
        $this->telegram = $telegramApi;
        $this->logChatId = $logChatId;
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

    public function isHooked()
    {
        $hookInfo = ArrayHelper::toObject($this->telegram->getWebhookInfo());
        return !empty($hookInfo->url);
    }

    public function hook($url, $certificate = '', $dropPendingUpdates = false)
    {
        if (empty($url)) {
            throw new ErrorException('Can not hook to an empty url');
        }

        $hookFileName = $this->deriveWebHookUniqueFilename(self::WEBHOOK_LOCK_FILENAME);
        if (file_exists($hookFileName)) {
            return;
        }

        if (!$this->isHooked()) {
            $this->telegram->setWebhook($url, $certificate, null, null, null, $dropPendingUpdates);

            //create the hook lock file as all went well:
            fclose(fopen($hookFileName, "a"));

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
        $hookFileName = $this->deriveWebHookUniqueFilename(self::WEBHOOK_LOCK_FILENAME);
        if (file_exists($hookFileName)) {
            $hookFilePointer = fopen($hookFileName, 'a');
            fclose($hookFilePointer);
            unlink($hookFileName);
        }
        $this->log('web hook deleted');
    }

    /**
     * Log to file and telegram test group
     */
    public function log($element, $title = null)
    {
        GuardeBotLogger::getInstance()->debug($title, [$element]);
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

        $messageChatId = null;

        if ($this->tryGetMessageChatId($update, $messageChatId)) {
            if ($messageChatId == $this->logChatId) {
                //ignore updates from the debug log group
                return true;
            }
        }

        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        $previousErrorReportingLevel = error_reporting(E_ALL);

        try {
            if (isset($update->message)) {
                //handle update message
                $this->processUpdate($update);
            }

            $this->setLastHandledUpdateInfo($updateId, time());
        } catch (\Throwable $e) {
            $this->handleException($e);
        } catch (\Exception $e) {
            $this->handleException($e);
        } finally {
            error_reporting($previousErrorReportingLevel);
            restore_error_handler();
            restore_exception_handler();
        }

        if (isset($this->lastHandledException)) {
            throw $this->lastHandledException;
        }

        return true;
    }

    private $lastHandledException;
    private function handleError($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

    private function handleException(\Throwable $e)
    {
        $this->lastHandledException = $e;
        $this->log($e, 'handleException');
    }

    /**
     * Handle update message and act based upon it
     * \param $update the telegram update
     */
    private function processUpdate($update)
    {
        $message = isset($update->message->text) ? $update->message->text : '';
        $spamValidator = new MlSpamTextValidator();
        $isValid = $spamValidator->validate($message);
        $commandText = '';
        $newMember = null;
        if (!$isValid) {
            //this is a spam
            //TODO: handle spam
            $this->log($update, 'Processing spam treatment ...');
        } else if ($this->isCommand($message, $commandText)) {
            $this->log('Processing command ...');
            $this->processCommand($commandText, $update);
        } else if ($this->isNewMemberIncoming($update, $newMember)) {
            //check if incoming user is marked as banned
            if (SpammersManager::getInstance()->has($newMember->userId) && !MastersManager::getInstance()->has($newMember->userId)) {
                $messageChatId = null;
                if ($this->tryGetMessageChatId($update, $messageChatId)) {
                    $this->banChatMember($messageChatId, $newMember);
                }
            }
        } else {
            $this->log($update, 'Unkown process update type !');
        }
    }

    /**
     * Whether or not the update concerns a new user incoming
     * \param $update The update to verify
     * \param $newMember The member info that will be populated if this is a new member incoming
     */
    private function isNewMemberIncoming($update, &$newMember): bool
    {
        if (
            isset($update->message)
            && isset($update->message->new_chat_member)
            && isset($update->message->new_chat_member->id)
        ) {
            return $this->tryGetMemberInfoFromStructure($update->message->new_chat_member, $newMember);
        }
        return false;
    }

    /**
     * Whether or not the given text is a command
     * \param $text the text to analyse
     * \param $commandText the extracted command text
     */
    private function isCommand($text, &$commandText): bool
    {
        $commandHeader = GuardeBotMessagesBase::getInstance()->get(GuardeBotMessagesBase::CMD_HEADER);
        $matches = [];
        if (preg_match(
            '/^(?i)(' . $commandHeader . ')\s*[!]*\s*[,;-]{0,1}\s*([\w\s]+)\s*[!]*\s*/',
            $text,
            $matches
        )) {
            $commandText = rtrim($matches[2]);
            return true;
        }
        return false;
    }

    private function processCommand($commandText, $update)
    {
        $loweredCommandText = strtolower($commandText);
        switch ($loweredCommandText) {
            case GuardeBotMessagesBase::getLowered(GuardeBotMessagesBase::CMD_MARK_AS_SPAM):
                //the behavior here is to considere this command is in a reply of the message to mark as spam
                $replyToMessageText = '';
                if ($this->tryGetReplyToMessageText($update, $replyToMessageText)) {
                    $learner = new MlSpamTextLearner();
                    $learner->learn($replyToMessageText, false);
                    $this->log($replyToMessageText, 'marked as spam !');
                }

                break;
            case GuardeBotMessagesBase::getLowered(GuardeBotMessagesBase::CMD_BAN_MESSAGE_AUTHOR):
                //the behavior here is to considere this command is in a reply of the message to mark the author as spammer
                $messageAuthorInfo = null;
                if ($this->tryGetMessageAuthorInfo($update, $messageAuthorInfo)) {
                    if (!MastersManager::getInstance()->has($messageAuthorInfo->userId)) {
                        SpammersManager::getInstance()->add($messageAuthorInfo->userId, $messageAuthorInfo->userName, $messageAuthorInfo->firstName, $messageAuthorInfo->lastName);
                        $authorDisplayName = $this->getBestMessageAuthorDisplayName($messageAuthorInfo);
                        $messageChatId = null;
                        if ($this->tryGetMessageChatId($update, $messageChatId)) {
                            if ($this->banChatMember($messageChatId, $messageAuthorInfo)) {
                                $this->say($messageChatId, GuardeBotMessagesBase::get(GuardeBotMessagesBase::ACK_BAN_MESSAGE_AUTHOR, [$authorDisplayName]));
                            }
                        }
                    }
                }
                break;
            default:
                $this->log('unrecognized command : ' . $commandText);
                break;
        }
    }

    private function banChatMember($chatId, $memberInfo)
    {
        if ($this->telegram->banChatMember(['chat_id' => $chatId, 'user_id' => $memberInfo->userId, 'until_date' => 0, 'revoke_messages' => true])) {
            $authorDisplayName = $this->getBestMessageAuthorDisplayName($memberInfo);
            return true;
        }
        return false;
    }

    private function getBestMessageAuthorDisplayName($messageAuthorInfo)
    {
        $displayName = '';

        if (!empty($messageAuthorInfo->firstName)) {
            $displayName .= $messageAuthorInfo->firstName;
        }

        if (!empty($messageAuthorInfo->lastName)) {
            if (!empty($displayName)) {
                $displayName .= ' ';
            }
            $displayName .= $messageAuthorInfo->lastName;
        }

        if (!empty($messageAuthorInfo->userName)) {
            if (!empty($displayName)) {
                $displayName .= ' ';
            }
            $displayName .= ('(@' . $messageAuthorInfo->userName . ')');
        }

        return $displayName;
    }

    private function tryGetMessageChatId($update, &$chatId): bool
    {
        if (
            isset($update->message)
            && isset($update->message->chat)
            && isset($update->message->chat->id)
        ) {
            $chatId = $update->message->chat->id;
            return true;
        }
        return false;
    }

    private function tryGetReplyToMessageText($update, &$message): bool
    {
        if (
            isset($update->message)
            && isset($update->message->reply_to_message)
            && !empty($update->message->reply_to_message->text)
        ) {
            $message = $update->message->reply_to_message->text;
            return true;
        }
        return false;
    }

    private function tryGetMessageAuthorInfo($update, &$messageAuthorInfo)
    {
        if (
            isset($update->message)
            && isset($update->message->reply_to_message)
        ) {
            $from = null;
            if (isset($update->message->reply_to_message->forward_from)) {
                $from = $update->message->reply_to_message->forward_from;
            } else {
                $from = $update->message->reply_to_message->from;
            }

            if (isset($from)) {
                return $this->tryGetMemberInfoFromStructure($from, $messageAuthorInfo);
            }
        }
        return false;
    }

    private function tryGetMemberInfoFromStructure($from, &$memberInfo)
    {
        if (isset($from)) {
            $memberInfo = (object)[
                'userId' => $from->id,
                'userName' => !empty($from->username) ? $from->username : '',
                'firstName' => !empty($from->first_name) ? $from->first_name : '',
                'lastName' => !empty($from->last_name) ? $from->last_name : ''
            ];
            return true;
        }
        return false;
    }
}
