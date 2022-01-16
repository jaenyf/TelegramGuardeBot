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
            self::CMD_HEADER => '/',
            self::FCMD_HEADER => 'Garde',
            self::CMD_MARK_AS_SPAM => 'spam',
            self::FCMD_MARK_AS_SPAM => 'Ceci est un spam',
            self::CMD_BAN_MESSAGE_AUTHOR => 'ban',
            self::FCMD_BAN_MESSAGE_AUTHOR => 'Bannis cet auteur',
            self::ACK_BAN_MESSAGE_AUTHOR => 'L\'utilisateur "%1" a été banni !',
            self::NEW_MEMBER_VALIDATION_MARKDOWN_GREETINGS => '_[@%1](tg://user?id=%2)_, bienvenue \\! Clique sur le boutton ci\\-dessous dans le temps imparti afin de rejoindre cet endroit \\! Merci \\! \\(%3 seconds\\)',
            self::NEW_MEMBER_VALIDATION_OTHER_MEMBER_CLICK_ERROR_MESSAGE => 'Seul l\'utilisateur %1 peut répondre à ce message',
            self::NEW_MEMBER_VALIDATION_BUTTON_TEXT => 'Je rejoins cet endroit !'
        );
    }
}
