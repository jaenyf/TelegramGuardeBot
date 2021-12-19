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
            self::CMD_HEADER => 'Guarde',
            self::CMD_MARK_AS_SPAM => 'This is a spam',
            self::CMD_BAN_MESSAGE_AUTHOR => 'Ban this author',
            self::ACK_BAN_MESSAGE_AUTHOR => 'User "%1" has been banned !'
        );
    }
}
