<?php

declare(strict_types=1);

namespace TelegramGuardeBot\i18n;

use TelegramGuardeBot\Helpers\MarkdownHelper;

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

        $shouldEscapeMarkdownParameters = (strpos($identifier, 'MARKDOWN') >=0);

        if (isset($parameters)) {
            $parameterCount = 0;
            foreach ($parameters as $parameter) {
                ++$parameterCount;
                if($shouldEscapeMarkdownParameters)
                {
                    $parameter = MarkdownHelper::escape(empty($parameter) ? '' : (string)$parameter);
                }
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
     * The header that will indentify a friendly command
     */
    public const FCMD_HEADER = 'FCMD_HEADER';

    /**
     * The command to mark a message as ham
     */
    public const CMD_MARK_AS_HAM = 'CMD_MARK_AS_HAM';

    /**
     * The friendly command to mark a message as ham
     */
    public const FCMD_MARK_AS_HAM = 'FCMD_MARK_AS_NO_SPAM';

    /**
     * The command to mark a message as spam
     */
    public const CMD_MARK_AS_SPAM = 'CMD_MARK_AS_SPAM';

    /**
     * The friendly command to mark a message as spam
     */
    public const FCMD_MARK_AS_SPAM = 'FCMD_MARK_AS_SPAM';

    /**
     * The command to ban a message author
     */
    public const CMD_BAN_MESSAGE_AUTHOR = 'CMD_BAN_MESSAGE_AUTHOR';

    /**
     * The friendly command to ban a message author
     */
    public const FCMD_BAN_MESSAGE_AUTHOR = 'FCMD_BAN_MESSAGE_AUTHOR';

    /**
     * The acknoledge of a banned message author
     * @param %1 member display name
     */
    public const ACK_BAN_MESSAGE_AUTHOR = 'ACK_BAN_MESSAGE_AUTHOR %1';

    /**
     * The greetings markdown message to welcome a member and require him to click a button under a specified amount of time
     * @param %1 member display name
     * @param %2 member id
     * @param %1 time in seconds
     */
    public const NEW_MEMBER_VALIDATION_MARKDOWN_GREETINGS = 'NEW_MEMBER_VALIDATION_MARKDOWN_GREETINGS %1 %2 %3';

    /**
     * The text displayed when another member click the join button
     */
    public const NEW_MEMBER_VALIDATION_OTHER_MEMBER_CLICK_ERROR_MESSAGE = 'NEW_MEMBER_VALIDATION_OTHER_MEMBER_CLICK_ERROR_MESSAGE %1';

    /**
     * The text displayed on the button a new member has to click to join
     */
    public const NEW_MEMBER_VALIDATION_BUTTON_TEXT = 'NEW_MEMBER_VALIDATION_BUTTON_TEXT';

    /**
     * The text displayed on the button a new member has to click to join
     */
    public const MESSAGE_LOOKS_LIKE_SPAM = 'MESSAGE_LOOKS_LIKE_SPAM';
}
