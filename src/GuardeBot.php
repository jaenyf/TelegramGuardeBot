<?php

namespace TelegramGuardeBot;

use TelegramGuardeBot\i18n\GuardeBotMessagesBase;
use TelegramGuardeBot\i18n\GuardeBotMessages;
use TelegramGuardeBot\GuardeBotLogger;
use TelegramGuardeBot\Validators\MlSpamTextValidator;
use TelegramGuardeBot\Learners\MlSpamTextLearner;
use TelegramGuardeBot\Managers\Spams\SpamAuthorsManager;

require_once('Telegram.php');

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
			throw new \Exception('Chat unique name has to be defined');
		}
		$this->blacklistFilename = $this->deriveUniqueChatFilename($blacklistFilename);
		$this->loadBlacklist();
		$this->logEnabled = $logEnabled;
		$this->telegram = new \Telegram($bot_token, $logEnabled, $proxy);
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

	/**
	 * returns a 2-elements array with the update id and the update date
	 */
	private function getLastHandledUpdateInfo()
	{
		$hookFileName = $this->deriveUniqueChatFilename(self::WEBHOOK_LOCK_FILENAME);
		$file = fopen($hookFileName, "r");
		$result = fgetcsv($file);
		if($result === false)
		{
			$result = ['0','0'];
		}
		fclose($file);
		return $result;
	}

	/**
	 * returns a 2-elements array with the update id and the update date
	 */
	private function setLastHandledUpdateInfo($updateId, $updateDate)
	{
		$hookFileName = $this->deriveUniqueChatFilename(self::WEBHOOK_LOCK_FILENAME);
		$file = fopen($hookFileName, "w");
		fputcsv($file, [$updateId, $updateDate]);
		fclose($file);
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

	public function hook($url, $certificate = '', $dropPendingUpdates = false)
	{
		$hookFileName = $this->deriveUniqueChatFilename(self::WEBHOOK_LOCK_FILENAME);
		if(file_exists($hookFileName)){
			return;
		}

		if(!$this->isHooked())
		{
			if(!$this->telegram->setWebhook($url, $certificate, $dropPendingUpdates))
			{
				throw new \Exception('Failed to set web hook');
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

	public function unHook($dropPendingUpdates = false)
	{
		if($this->isHooked())
		{
			if(!$this->telegram->deleteWebhook($dropPendingUpdates))
			{
				throw new \Exception('Failed to delete web hook');
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

		$elementText = GuardeBotLogger::log($element, $title);
		echo $elementText;
		$this->say($this->logChatId, $elementText);
	}

	private function say($chatId, $message)
	{
		$this->telegram->sendMessage(
			array(
				'chat_id' => $chatId,
				'text' => $message)
		);
	}
	

	/**
	 * Process the updates received by the web hook
	 * \param $updates the updates in json format
	 */
	public function processUpdate($update)
	{
		$updateId = $update->update_id;
		$updateInfo = $this->getLastHandledUpdateInfo();
		if($updateId == $updateInfo[0])
		{
			//this update has already been processed
			return;
		}

		$messageChatId = null;

		if($this->tryGetMessageChatId($update, $messageChatId))
		{
			if($messageChatId == $this->logChatId)
			{
				//ignore updates from the debug log group
				return true;
			}
		}

		try
		{
			if(isset($update->message) && !empty($update->message->text))
			{
				//handle update message
				$this->processUpdateMessage($update);
			}

			$this->setLastHandledUpdateInfo($updateId, time());
			return true;
		}
		catch(\Throwable $e)
		{
			$this->log($e, 'processUpdate - exception');
			return false;
		}
		catch (\Exception $e) {
			$this->log($e, 'processUpdate - exception');
			return false;
		}
	}

	/**
	 * Handle update message and act based upon it
	 * \param $update the telegram update
	 */
	private function processUpdateMessage($update)
	{
		$message = $update->message->text;
		$spamValidator = new MlSpamTextValidator();
		$isValid = $spamValidator->validate($message);
		if(!$isValid)
		{
			//this is a spam
			//TODO: handle spam
		}
		else
		{
			$commandText = '';
			if($this->isCommand($message, $commandText))
			{
				$this->processCommand($commandText, $update);
			}
		}
	}

	/**
	 * Whether or not the given text is a command
	 * \param $text the text to analyse
	 * \param $commandText the extracted command text
	 */
	private function isCommand($text, &$commandText) : bool
	{
		$commandHeader = GuardeBotMessagesBase::getInstance()->get(GuardeBotMessagesBase::CMD_HEADER);
		$matches = [];
		if(preg_match(
			'/^(?i)('.$commandHeader.')\s*[!]*\s*[,;-]{0,1}\s*([\w\s]+)\s*[!]*\s*/',
			$text,
			$matches
		))
		{
			$commandText = rtrim($matches[2]);
			return true;
		}
		return false;
	}

	private function processCommand($commandText, $update)
	{
		$loweredCommandText = strtolower($commandText);
		switch($loweredCommandText)
		{
			case GuardeBotMessagesBase::getLowered(GuardeBotMessagesBase::CMD_MARK_AS_SPAM):
				//the behavior here is to considere this command is in a reply of the message to mark as spam
				$replyToMessageText = '';
				if($this->tryGetReplyToMessageText($update, $replyToMessageText))
				{
					$learner = new MlSpamTextLearner();
					$learner->learn($replyToMessageText, false);
					$this->log($replyToMessageText, 'marked as spam !');
				}
				
				break;
			case GuardeBotMessagesBase::getLowered(GuardeBotMessagesBase::CMD_BAN_MESSAGE_AUTHOR):
				//the behavior here is to considere this command is in a reply of the message to mark the author as spammer
				$messageAuthorInfo = null;
				if($this->tryGetMessageAuthorInfo($update, $messageAuthorInfo))
				{
					$spamAuthorsManager = new SpamAuthorsManager();
					$spamAuthorsManager->addGlobal($messageAuthorInfo->userId, $messageAuthorInfo->userName, $messageAuthorInfo->firstName, $messageAuthorInfo->lastName);
					$messageChatId = null;
					if($this->tryGetMessageChatId($update, $messageChatId))
					{
						if($this->telegram->banChatMember(['chat_id' => $messageChatId, 'user_id' => $messageAuthorInfo->userId, 'until_date' => 0, 'revoke_messages' => true]))
						{
							$authorDisplayName = $this->getBestMessageAuthorDisplayName($messageAuthorInfo);
							$this->say($messageChatId, GuardeBotMessagesBase::get(GuardeBotMessagesBase::ACK_BAN_MESSAGE_AUTHOR, [$authorDisplayName]));
						}
					}
					$this->log($spamAuthorsManager, 'Author marked as spammer !');
				}
				break;
			default:
				$this->log('unrecognized command : '.$commandText);
				break;
		}
	}

	private function getBestMessageAuthorDisplayName($messageAuthorInfo)
	{
		$displayName = '';

		if(!empty($messageAuthorInfo->firstName))
		{
			$displayName .= $messageAuthorInfo->firstName;
		}

		if(!empty($messageAuthorInfo->lastName))
		{
			if(!empty($displayName))
			{
				$displayName .= ' ';
			}
			$displayName .= $messageAuthorInfo->lastName;
		}

		if(!empty($messageAuthorInfo->userName))
		{
			if(!empty($displayName))
			{
				$displayName .= ' ';
			}
			$displayName .= ('(@'.$messageAuthorInfo->userName.')');
		}

		return $displayName;
	}

	private function tryGetMessageChatId($update, &$chatId) : bool
	{
		if(
			isset($update->message)
			&& isset($update->message->chat)
			&& isset($update->message->chat->id))
		{
			$chatId = $update->message->chat->id;
			return true;
		}
		return false;
	}

	private function tryGetReplyToMessageText($update, &$message) : bool
	{
		if(
			isset($update->message)
			&& isset($update->message->reply_to_message)
			&& !empty($update->message->reply_to_message->text))
		{
			$message = $update->message->reply_to_message->text;
			return true;
		}
		return false;
	}

	private function tryGetMessageAuthorInfo($update, &$messageAuthorInfo)
	{
		if(
			isset($update->message)
			&& isset($update->message->reply_to_message))
		{
			$from = null;
			if(isset($update->message->reply_to_message->forward_from))
			{
				$from = $update->message->reply_to_message->forward_from;
			}
			else
			{
				$from = $update->message->reply_to_message->from;
			}

			if(isset($from))
			{
				$messageAuthorInfo = (object)[
					'userId' => $from->id,
					'userName' => !empty($from->username) ? $from->username : '',
					'firstName' => !empty($from->first_name) ? $from->first_name : '',
					'lastName' => !empty($from->last_name) ? $from->last_name : ''
				];
			}

			return true;
		}
		return false;
	}
}
