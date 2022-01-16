<?php

declare(strict_types=1);

namespace TelegramGuardeBot\i18n;

use TelegramGuardeBot\i18n\GuardeBotMessagesBase;

class GuardeBotMessages extends GuardeBotMessagesBase
{
    public function __construct()
    {
        parent::__construct();
        $this->messages = array(
            self::SAY_WEBHOOK_SET_UP => 'Web hook set up !',
            self::CMD_HEADER => '/',
            self::FCMD_HEADER => 'Guarde',
            self::CMD_MARK_AS_SPAM => 'spam',
            self::FCMD_MARK_AS_SPAM => 'This is a spam',
            self::CMD_BAN_MESSAGE_AUTHOR => 'ban',
            self::FCMD_BAN_MESSAGE_AUTHOR => 'Ban this author',
            self::ACK_BAN_MESSAGE_AUTHOR => 'User "%1" has been banned !',
            self::NEW_MEMBER_VALIDATION_MARKDOWN_GREETINGS => '_[@%1](tg://user?id=%2)_, welcome\\! Click the button bellow under the specified amount of time to join this place\\! Thank you in advance\\! \\(%3 seconds\\)',
            self::NEW_MEMBER_VALIDATION_OTHER_MEMBER_CLICK_ERROR_MESSAGE => 'Only user %1 could reply to this message',
            self::NEW_MEMBER_VALIDATION_BUTTON_TEXT => 'I join this place!'
        );
    }
}
