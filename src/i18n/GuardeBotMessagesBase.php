<?php

declare(strict_types=1);

namespace TelegramGuardeBot\i18n;

class GuardeBotMessagesBase
{
    private static $instance = null;
    protected $messages;

    public function __construct()
    {
        $this->messages = [];
    }

    public static function getInstance()
    {
        return (self::$instance != null) ? self::$instance : (self::$instance = new GuardeBotMessages());
    }

    protected function getByIdentifier($identifier)
    {
        return $this->messages[$identifier];
    }

    /**
     * Retrieve a localized message based on its identifier
     * \param $identifier The identifier of the message
     * \param $parameters An array of strings to replace the variable part of the message
     */
    public static function get($identifier, array $parameters = null)
    {
        $result = self::getInstance()->getByIdentifier($identifier);

        if (isset($parameters)) {
            $parameterCount = 0;
            foreach ($parameters as $parameter) {
                ++$parameterCount;
                $result = str_replace(('%' . $parameterCount), $parameter, $result);
            }
        }

        return $result;
    }

    /**
     * Retrieve a lowered localized message based on its identifier
     * \param $identifier The identifier of the message
     * \param $parameters An array of strings to replace the variable part of the message
     */
    public static function getLowered($identifier, array $parameters = null)
    {
        return strtolower(self::get($identifier, $parameters));
    }

    /**
     * The message said once the webhook has been set up
     */
    public const SAY_WEBHOOK_SET_UP = 'SAY_WEBHOOK_SET_UP';

    /**
     * The header that will indentify a command
     */
    public const CMD_HEADER = 'CMD_HEADER';

    /**
     * The command to mark a message as spam
     */
    public const CMD_MARK_AS_SPAM = 'CMD_MARK_AS_SPAM';

    /**
     * The command to ban a message author
     */
    public const CMD_BAN_MESSAGE_AUTHOR = 'CMD_BAN_MESSAGE_AUTHOR';

    /**
     * The acknoledge of a banned message author
     */
    public const ACK_BAN_MESSAGE_AUTHOR = 'ACK_BAN_MESSAGE_AUTHOR %1';
}
