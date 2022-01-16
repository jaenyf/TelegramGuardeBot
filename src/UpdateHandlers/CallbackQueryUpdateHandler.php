<?php

declare(strict_types=1);

namespace TelegramGuardeBot\UpdateHandlers;

use TelegramGuardeBot\TelegramApi;
use TelegramGuardeBot\UpdateHandlers\UpdateHandler;
use TelegramGuardeBot\Helpers\TelegramHelper;
use TelegramGuardeBot\Workers\Scheduler;
use TelegramGuardeBot\Workers\MemberValidationApprovalTask;
use TelegramGuardeBot\i18n\GuardeBotMessagesBase;

/**
 * Handle a CallbackQuery Telegram update
 */
class CallbackQueryUpdateHandler extends UpdateHandler
{
    private TelegramApi $telegram;
    public function __construct(TelegramApi $telegram)
    {
        $this->telegram = $telegram;
    }

    public function handle($update, $callbackQuery = null)
    {
        //process the callback query
        $callbackQueryData = explode('|', isset($callbackQuery->data) ? $callbackQuery->data : '');
        $callbackQueryDataCount = count($callbackQueryData);
        if ($callbackQueryDataCount) {
            $callbackQueryId = $callbackQuery->id;
            switch ($callbackQueryData[0]) {


                case 'MemberValidationClick':

                    /**
                     * A member has clicked the validation button,
                     * checked it is the authorized user and discard the button
                     */
                    if ($callbackQueryDataCount === 4)
                    {
                        $memberId = (int)$callbackQueryData[1];
                        $currentTime = time();
                        $arrivalTime = (int)$callbackQueryData[2];
                        $graceTime = (int)$callbackQueryData[3];

                        $callbackAuthorInfo = null;
                        if (TelegramHelper::tryGetMemberInfoFromStructure($callbackQuery->from, $callbackAuthorInfo))
                        {
                            $messageChatId = null;
                            if (TelegramHelper::tryGetMessageChatId($callbackQuery, $messageChatId))
                            {
                                if ($memberId !== $callbackAuthorInfo->userId)
                                {
                                    /**
                                     * Unauthorized user clicked the button
                                     */
                                    $callbackQueryTargetMemberResult  = $this->telegram->getChatMember(['chat_id' => $messageChatId, 'user_id' => $memberId]);
                                    $callbackQueryTargetMember = (isset($callbackQueryTargetMemberResult->user))
                                        ? $callbackQueryTargetMemberResult->user
                                        : null;
                                    if (isset($callbackQueryTargetMember))
                                    {
                                        $targetMemberInfo = null;
                                        if (TelegramHelper::tryGetMemberInfoFromStructure($callbackQueryTargetMember, $targetMemberInfo)) {
                                            $targetMemberDisplayName = TelegramHelper::getBestMessageAuthorDisplayName($targetMemberInfo);
                                            $this->telegram->answerCallbackQuery([
                                                'callback_query_id' => $callbackQueryId,
                                                'text' => GuardeBotMessagesBase::get(GuardeBotMessagesBase::NEW_MEMBER_VALIDATION_OTHER_MEMBER_CLICK_ERROR_MESSAGE, [$targetMemberDisplayName]),
                                                'show_alert' => false
                                            ]);
                                        }
                                    }
                                }
                                else
                                {
                                    /**
                                     * Authorized user clicked the button
                                     */
                                    if(($arrivalTime + $graceTime) <= $currentTime)
                                    {
                                        $approvalTask = new MemberValidationApprovalTask($messageChatId, $memberId);
                                        Scheduler::getInstance()->addTask($approvalTask);
                                    }

                                    $this->telegram->deleteMessage(['chat_id' => $messageChatId, 'message_id' => $callbackQuery->message->message_id]);
                                }
                            }
                        }
                    }
                    else
                    {
                        throw new \ErrorException("Invalid MemberValidationClick format : ".$callbackQuery->data);
                    }
                    break;
            }
        }
    }
}
