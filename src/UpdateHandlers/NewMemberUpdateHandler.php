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
        $messageChatId = $update->chat_member->chat->id;

        App::getInstance()->getLogger()->info("Incoming member '" . TelegramHelper::getBestMessageAuthorDisplayName($newMemberInfo, true) . "'...");

        //check if incoming user is marked as banned
        if (SpammersManager::getInstance()->has($newMemberInfo->userId) && !MastersManager::getInstance()->has($newMemberInfo->userId))
        {
            App::getInstance()->getLogger()->info("Banning incoming member '" . TelegramHelper::getBestMessageAuthorDisplayName($newMemberInfo, true) . "' because of blacklist...");
            TelegramHelper::banChatMember($this->telegram, $messageChatId, $newMemberInfo);
        }
        else if(App::getInstance()->getDIContainer('appConfig')->enableNewMemberValidation)
        {
            if ($update->chat_member->new_chat_member->status != 'creator')
            {
                //restrict user
                $this->telegram->restrictChatMember([
                    'chat_id' => $messageChatId,
                    'user_id' => $newMemberInfo->userId,
                    'until_date' => time() + 30,
                    'permissions' => [
                        'can_send_messages' => false,
                        'can_send_media_messages' => false,
                        'can_send_polls' => false,
                        'can_send_other_messages' => false,
                        'can_add_web_page_previews' => false,
                        'can_change_info' => false,
                        'can_invite_users' => false,
                        'can_pin_messages' => false
                    ]
                ]);
            }

            //register pending new member validation
            $manager = new NewMembersValidationManager();
            $manager->add($messageChatId, $newMemberInfo->userId);

            //show validation keyboard button
            $currentSleepTime = App::getInstance()->getDIContainer('appConfig')->newMemberValidationTimeout;
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
            App::getInstance()->getScheduler()->addTask($ejectionTask);
        }
    }
}
