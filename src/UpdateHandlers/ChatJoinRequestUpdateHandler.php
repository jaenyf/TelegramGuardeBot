<?php

declare(strict_types=1);

namespace TelegramGuardeBot\UpdateHandlers;

use TelegramGuardeBot\App;
use TelegramGuardeBot\TelegramApi;
use TelegramGuardeBot\UpdateHandlers\UpdateHandler;
use TelegramGuardeBot\Managers\Masters\MastersManager;
use TelegramGuardeBot\Managers\Spams\SpammersManager;
use TelegramGuardeBot\Helpers\TelegramHelper;

/**
 * Handle an Chat Join Request Telegram update
 */
class ChatJoinRequestUpdateHandler extends UpdateHandler
{
    private TelegramApi $telegram;
    public function __construct(TelegramApi $telegram)
    {
        $this->telegram = $telegram;
    }
    public function handle($update, $newMemberInfo = null)
    {
        $messageChatId = $update->chat_member->chat->id;

        App::getInstance()->getLogger()->info("Chat join request for '" . TelegramHelper::getBestMessageAuthorDisplayName($newMemberInfo, true) . "' [".implode(',', [$newMemberInfo->userId, $newMemberInfo->userName, $newMemberInfo->firstName, $newMemberInfo->lastName])."] ...");

        if(MastersManager::getInstance()->has($newMemberInfo->userId))
        {
            $messageChatId = null;
            if (TelegramHelper::tryGetMessageChatId($update, $messageChatId))
            {
                App::getInstance()->getLogger()->info("Approving chat join request for member '" . TelegramHelper::getBestMessageAuthorDisplayName($newMemberInfo, true) . "' because of masterlist...");
                TelegramHelper::approveChatJoinRequest($this->telegram, $messageChatId, $newMemberInfo);
            }
        }
        else if (SpammersManager::getInstance()->has($newMemberInfo->userId))
        {
            $messageChatId = null;
            if (TelegramHelper::tryGetMessageChatId($update, $messageChatId))
            {
                App::getInstance()->getLogger()->info("Declining chat join request for member '" . TelegramHelper::getBestMessageAuthorDisplayName($newMemberInfo, true) . "' because of blacklist...");
                TelegramHelper::declineChatJoinRequest($this->telegram, $messageChatId, $newMemberInfo);
            }
        }
    }
}
