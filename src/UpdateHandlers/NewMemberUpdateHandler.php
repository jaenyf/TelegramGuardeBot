<?php

declare(strict_types=1);

namespace TelegramGuardeBot\UpdateHandlers;

use TelegramGuardeBot\App;
use TelegramGuardeBot\TelegramApi;
use TelegramGuardeBot\Managers\NewMembersValidationManager;
use TelegramGuardeBot\Managers\Masters\MastersManager;
use TelegramGuardeBot\Managers\Spams\SpammersManager;
use TelegramGuardeBot\Helpers\TelegramHelper;
use TelegramGuardeBot\UpdateHandlers\UpdateHandler;
use TelegramGuardeBot\Workers\Scheduler;
use TelegramGuardeBot\Workers\MemberValidationEjectionTask;
use TelegramGuardeBot\i18n\GuardeBotMessagesBase;

/**
 * Handle an new member Telegram update
 */
class NewMemberUpdateHandler extends UpdateHandler
{
    private TelegramApi $telegram;
    public function __construct(TelegramApi $telegram)
    {
        $this->telegram = $telegram;
    }
    public function handle($update, $newMemberInfo = null)
    {
        $messageChatId = $update->message->chat->id;

        if(TelegramHelper::isMe($this->telegram, $newMemberInfo->userId))
        {
            App::getInstance()->getLogger()->debug("Ignoring Incoming member (me) '" . TelegramHelper::getBestMessageAuthorDisplayName($newMemberInfo, true) . "'...");
            return;
        }

        App::getInstance()->getLogger()->info("Incoming member '" . TelegramHelper::getBestMessageAuthorDisplayName($newMemberInfo, true) . "'...");

        //check if incoming user is marked as banned
        if (SpammersManager::getInstance()->has($newMemberInfo->userId) && !MastersManager::getInstance()->has($newMemberInfo->userId))
        {
            App::getInstance()->getLogger()->info("Banning incoming member '" . TelegramHelper::getBestMessageAuthorDisplayName($newMemberInfo, true) . "' because of blacklist...");
            TelegramHelper::banChatMember($this->telegram, $messageChatId, $newMemberInfo);
        }
        else if(App::getInstance()->enableNewMemberValidation)
        {
            App::getInstance()->getLogger()->info("Processing incoming member validation for '" . TelegramHelper::getBestMessageAuthorDisplayName($newMemberInfo, true) . "'...");

            //register pending new member validation
            $manager = new NewMembersValidationManager();

            //Check we do not reprocess a pending validation, as telegram may send multiple similar requests
            if(!$manager->has($messageChatId, $newMemberInfo->userId))
            {
                $muteSucceeded = App::getInstance()->getBot()->muteMember($messageChatId, $newMemberInfo->userId);

                if($muteSucceeded)
                {
                    $manager->add($messageChatId, $newMemberInfo->userId);

                    //show validation keyboard button
                    $currentSleepTime = App::getInstance()->newMemberValidationTimeout;
                    $authorDisplayName = TelegramHelper::getBestMessageAuthorDisplayName($newMemberInfo);

                    $keyboardMessage = [
                        'chat_id' => $messageChatId,
                        'text' => GuardeBotMessagesBase::get(GuardeBotMessagesBase::NEW_MEMBER_VALIDATION_MARKDOWN_GREETINGS, [$authorDisplayName, $newMemberInfo->userId, $currentSleepTime]),
                        'parse_mode' => 'MarkdownV2',
                        'has_protected_content' => true,
                        'reply_markup' => json_encode([
                            'inline_keyboard' => [[
                                [
                                    'text' => GuardeBotMessagesBase::get(GuardeBotMessagesBase::NEW_MEMBER_VALIDATION_BUTTON_TEXT),
                                    'callback_data' => 'MemberValidationClick|' . $newMemberInfo->userId . '|' . time() . '|' . $currentSleepTime
                                ],
                            ]]
                        ])
                    ];

                    $sentKeyboardMessage = $this->telegram->sendMessage($keyboardMessage);

                    $ejectionTask = new MemberValidationEjectionTask($messageChatId, $newMemberInfo->userId, $sentKeyboardMessage->message_id);
                    $ejectionTask->setNextRunTime(time() + $currentSleepTime);
                    Scheduler::getInstance()->addTask($ejectionTask);
                }
                else
                {
                    App::getInstance()->getLogger()->info("Discarding processing incoming member validation for '" . TelegramHelper::getBestMessageAuthorDisplayName($newMemberInfo, true) . "' because of unsuccessful mute");
                }
            }
            else
            {
                App::getInstance()->getLogger()->info("Discarding processing incoming member validation for '" . TelegramHelper::getBestMessageAuthorDisplayName($newMemberInfo, true) . "' because it is already in progress ...");
            }
        }
    }
}
