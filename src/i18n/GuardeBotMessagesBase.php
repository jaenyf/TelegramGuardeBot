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

	public static function get($identifier)
	{
		return self::getInstance()->getByIdentifier($identifier);
	}

	public static function getLowered($identifier)
	{
		return strtolower(self::get($identifier));
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
}
