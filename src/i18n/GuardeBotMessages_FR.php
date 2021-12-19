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
            self::SAY_WEBHOOK_SET_UP => 'Web hook en place !',
            self::CMD_HEADER => 'Garde',
            self::CMD_MARK_AS_SPAM => 'Ceci est un spam',
            self::CMD_BAN_MESSAGE_AUTHOR => 'Bannis cet auteur',
            self::ACK_BAN_MESSAGE_AUTHOR => 'L\'utilisateur "%1" a été banni !'
        );
    }
}
