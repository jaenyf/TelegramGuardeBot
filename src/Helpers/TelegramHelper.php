<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Helpers;

/**
 * Helper for Telegram Api
 */
class TelegramHelper
{
    public static function tryGetMessageChatId($update, &$chatId): bool
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

    public static function isCallbackQuery($update, &$callbackQuery): bool
    {
        if (isset($update->callback_query)) {
            $callbackQuery = $update->callback_query;
            return true;
        }
        return false;
    }

    public static function tryGetReplyToMessageText($update, &$message): bool
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


    /**
     * Extract the telegram result from the response if its status is ok
     */
    public static function extractTelegramResponseResult(array $response)
    {
        if (isset($response)) {
            if (array_key_exists('ok', $response)) {
                if ($response['ok']) {
                    if (array_key_exists('result', $response)) {
                        return (object)($response['result']);
                    }
                }
            }
        }
        return false;
    }

    public static function tryGetMemberInfoFromStructure($from, &$memberInfo)
    {
        if (isset($from)) {
            $from = (object)$from;
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

    public static function tryGetMessageAuthorInfo($update, &$messageAuthorInfo)
    {
        if (isset($update->message)) {
            $from = null;
            if (isset($update->message->reply_to_message)) {
                if (isset($update->message->reply_to_message->forward_from)) {
                    $from = $update->message->reply_to_message->forward_from;
                } else {
                    $from = $update->message->reply_to_message->from;
                }
            } else if (isset($update->message->from)) {
                $from = $update->message->from;
            }

            if (isset($from)) {
                return TelegramHelper::tryGetMemberInfoFromStructure($from, $messageAuthorInfo);
            }
        }
        return false;
    }

    /**
     * Whether or not the update concerns a new user incoming
     * \param $update The update to verify
     * \param $newMember The member info that will be populated if this is a new member incoming
     */
    public static function isNewMemberIncoming($update, &$newMember): bool
    {
        if (
            isset($update->chat_member)
            && isset($update->chat_member->new_chat_member)
            && isset($update->chat_member->new_chat_member->user)
            && isset($update->chat_member->new_chat_member->status)
            && ($update->chat_member->new_chat_member->status == 'member' || $update->chat_member->new_chat_member->status == 'administrator' || $update->chat_member->new_chat_member->status == 'creator')
        ) {
            return TelegramHelper::tryGetMemberInfoFromStructure($update->chat_member->new_chat_member->user, $newMember);
        }
        return false;
    }

    /**
     * Whether or not the update concerns a new chat joind request
     * \param $update The update to verify
     * \param $newMember The member info that will be populated if this is a new chat join request
     */
    public static function isChatJoinRequest($update, &$newMember) : bool
    {
        if (
            isset($update->chat_join_request)
            && isset($update->chat_join_request->from)
            && isset($update->chat_join_request->from->id)
        ) {
            return TelegramHelper::tryGetMemberInfoFromStructure($update->chat_join_request->from, $newMember);
        }
        return false;
    }


    public static function getBestMessageAuthorDisplayName($messageAuthorInfo, bool $showUserId = false) : string
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

        if($showUserId)
        {
            if (!empty($messageAuthorInfo->userId)) {
                if (!empty($displayName)) {
                    $displayName .= ' ';
                }
                $displayName .= ('(' . $messageAuthorInfo->userId . ')');
            }
        }

        return $displayName;
    }

    public static function approveChatJoinRequest($telegram, $chatId, $memberInfo)
    {
        if ($telegram->approveChatJoinRequest(['chat_id' => $chatId, 'user_id' => $memberInfo->userId])) {
            return true;
        }
        return false;
    }

    public static function declineChatJoinRequest($telegram, $chatId, $memberInfo)
    {
        if ($telegram->declineChatJoinRequest(['chat_id' => $chatId, 'user_id' => $memberInfo->userId])) {
            return true;
        }
        return false;
    }

    public static function banChatMember($telegram, $chatId, $memberInfo)
    {
        if ($telegram->banChatMember(['chat_id' => $chatId, 'user_id' => $memberInfo->userId, 'until_date' => 0, 'revoke_messages' => true])) {
            return true;
        }
        return false;
    }
}
