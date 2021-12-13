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
			self::SAY_WEBHOOK_SET_UP => 'Web hook en place !'
		);
	}
}
