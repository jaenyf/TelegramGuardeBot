<?php

namespace TelegramGuardeBot;

use TelegramGuardeBot\Helpers\ArrayHelper;

require_once fromAppSource('Telegram.php');

/**
 * A bridge to interact with the Telegram Bot API
 * Mainly used to throw on endpoint bad response
 */
class TelegramApi
{
    private $api;
    public function __construct(string $token, bool $logErrors = true, $proxy = [])
    {
        $this->api = new \Telegram($token, $logErrors, $proxy);
    }

    /**
     * Check if a Telegram API response is ok
     */
    private static function isOk($response)
    {
        $response = (object)$response;
        return $response->ok == true;
    }

    /**
     * Get the error message from a Telegram API response is ok
     */
    private static function getErrorMessage($response)
    {
        $response = (object)$response;
        return '(' . $response->error_code . ') ' . $response->description;
    }

    private static function getResultOrThrow($response, bool $throw = true)
    {
        if (!TelegramApi::isOk($response) && $throw) {
            throw new \ErrorException(TelegramApi::getErrorMessage($response));
        }
        return ArrayHelper::toObject(((object)$response)->result);
    }



    public function answerCallbackQuery(array $content)
    {
        return TelegramApi::getResultOrThrow($this->api->answerCallbackQuery($content));
    }

    public function approveChatJoinRequest(array $content)
    {
        return TelegramApi::getResultOrThrow($this->api->approveChatJoinRequest($content));
    }

    public function banChatMember(array $content)
    {
        return TelegramApi::getResultOrThrow($this->api->banChatMember($content));
    }

    public function deleteMessage(array $content, bool $throw = true)
    {
        return TelegramApi::getResultOrThrow($this->api->deleteMessage($content), $throw);
    }

    public function declineChatJoinRequest(array $content)
    {
        return TelegramApi::getResultOrThrow($this->api->declineChatJoinRequest($content));
    }

    public function deleteWebhook($dropPendingUpdates = false)
    {
        return TelegramApi::getResultOrThrow($this->api->deleteWebhook($dropPendingUpdates));
    }

    public function getChatMember(array $content)
    {
        return TelegramApi::getResultOrThrow($this->api->getChatMember($content));
    }

    public function getMe()
    {
        return TelegramApi::getResultOrThrow($this->api->getMe());
    }

    public function getWebhookInfo()
    {
        return TelegramApi::getResultOrThrow($this->api->getWebhookInfo());
    }

    public function restrictChatMember(array $content)
    {
        return TelegramApi::getResultOrThrow($this->api->restrictChatMember($content));
    }

    public function sendMessage(array $content)
    {
        return TelegramApi::getResultOrThrow($this->api->sendMessage($content));
    }

    public function setWebhook($url, $certificate = '', $dropPendingUpdates = false)
    {
        return TelegramApi::getResultOrThrow($this->api->setWebhook($url, $certificate, $dropPendingUpdates));
    }

    public function unbanChatMember(array $content)
    {
        return TelegramApi::getResultOrThrow($this->api->unbanChatMember($content));
    }
}
