<?php

declare(strict_types=1);

namespace TelegramGuardeBot\i18n;

class GuardeBotMessagesBase
{
	private static $instance = null;
	
	public const SAY_WEBHOOK_SET_UP = 'SAY_WEBHOOK_SET_UP';
	
	protected $messages;
	
	public function __construct()
	{
		$this->messages = [];
	}
	
	public static function getInstance()
	{
		return (self::$instance != null) ? self::$instance : (self::$instance = new GuardeBotMessages());
	}
	
	public function get($identifier)
	{
		return $this->messages[$identifier];
	}
}
