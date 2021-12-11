<?php

require_once('Telegram.php');
require_once('i18n/GuardeBotMessagesBase.php');
require_once('GuardeBotLogger.php');

/**
 * GuardeBot Class.
 *
 * @author jaenyf
 */
class GuardeBot
{
	/**
     * Constant for the blacklist file name.
     */
	const BLACKLIST_FILENAME = 'blacklist.lst';

	/**
     * Constant for the webhook lock file name.
     */
	const WEBHOOK_LOCK_FILENAME = 'guardebot.lock';

	private $telegram = null;
	private $chatUniqueName = null;
	private $blacklistFilename = null;
	private $blacklist = null;
	private $logEnabled = true;
	private $logChatId = null;

    /**
     * Create a GuardeBot instance
	 * \param $bot_token the bot token
	 * \param $log_errors enable or disable the logging
     * \param $proxy array with the proxy configuration (url, port, type, auth)
     * \return an instance of the class.
     */
    public function __construct(
		$bot_token,
		$chat_unique_name,
		$blacklistFilename = self::BLACKLIST_FILENAME,
		$logEnabled = true,
		array $proxy = []
	)
    {
		$this->chatUniqueName = trim(isset($chat_unique_name) ? $chat_unique_name : '');
		if($this->chatUniqueName === ''){
			throw new Exception('Chat unique name has to be defined');
		}
		$this->blacklistFilename = $this->deriveUniqueChatFilename($blacklistFilename);
		$this->loadBlacklist();
		$this->logEnabled = $logEnabled;
		$this->telegram = new Telegram($bot_token, $logEnabled, $proxy);
    }

	private function loadBlacklist()
	{
		//create the blacklist file if necessary:
		if(!file_exists($this->blacklistFilename)){
			fclose(fopen($this->blacklistFilename, "w"));
		}
		//load the blacklist file
		$this->blacklist = file($this->blacklistFilename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	}

	private function deriveUniqueChatFilename($baseFilename)
	{
		$baseFilenameExt = pathinfo($baseFilename, PATHINFO_EXTENSION);
		return basename($baseFilename, '.'.$baseFilenameExt) . '_' . $this->chatUniqueName . '.' . $baseFilenameExt;
	}

	public function isHooked()
	{
		$hookInfo = $this->telegram->getWebhookInfo();
		$this->log($hookInfo, 'hookInfo:');
		if(!isset($hookInfo))
		{
			return false;
		}
		return !empty($hookInfo->url);
	}

	public function hook($url, $certificate = '')
	{
		$hookFileName = $this->deriveUniqueChatFilename(self::WEBHOOK_LOCK_FILENAME);
		if(file_exists($hookFileName)){
			return;
		}

		if(!$this->isHooked())
		{
			if(!$this->telegram->setWebhook($url, $certificate))
			{
				throw new Exception('Failed to set web hook');
			}
			
			//create the hook lock file as all went well:
			fclose(fopen($hookFileName, "w"));
			
			$this->log('web hook set');
		}
		else
		{
			$this->log('web hook already set');
		}
	}

	public function unHook()
	{
		if($this->isHooked())
		{
			if(!$this->telegram->deleteWebhook())
			{
				throw new Exception('Failed to delete web hook');
			}
		}
		if(file_exists(self::WEBHOOK_LOCK_FILENAME))
		{
			$hookFilePointer = fopen(self::WEBHOOK_LOCK_FILENAME, 'w+'); 
			fclose($$hookFilePointer);
			unlink($$hookFilePointer);
		}
		$this->log('web hook deleted');
	}

	public function setLogChatId($logChatId)
	{
		$this->logChatId = $logChatId;
	}

	/**
	 * Log to file and telegram test group
	 */
	public function log($element, $title=null)
	{
		if(!$this->logEnabled)
		{
			return;
		}
		
		if (class_exists('GuardeBotLogger'))
		{
			$elementText = GuardeBotLogger::log($element, $title);
			$this->telegram->sendMessage(
				array(
					'chat_id' => $this->logChatId,
					'text' => $elementText)
			);
		}
	}
	

	/**
	 * Process the updates received by the web hook
	 * \param $updates the updates in json format
	 */
	public function processUpdate($update)
	{
		$this->log($update);
		return true;
	}
}
