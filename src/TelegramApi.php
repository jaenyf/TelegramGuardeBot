<?php

namespace TelegramGuardeBot;

use ErrorException;

require_once('Telegram.php');

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

    private static function getResultOrThrow($response)
    {
        if (!TelegramApi::isOk($response)) {
            throw new ErrorException(TelegramApi::getErrorMessage($response));
        }
        return ((object)$response)->result;;
    }

    public function getMe()
    {
        return TelegramApi::getResultOrThrow($this->api->getMe());
    }

    public function sendMessage(array $content)
    {
        return TelegramApi::getResultOrThrow($this->api->sendMessage($content));
    }

    public function kickChatMember(array $content)
    {
        return TelegramApi::getResultOrThrow($this->api->kickChatMember($content));
    }

    public function banChatMember(array $content)
    {
        return TelegramApi::getResultOrThrow($this->api->banChatMember($content));
    }

    public function unbanChatMember(array $content)
    {
        return TelegramApi::getResultOrThrow($this->api->unbanChatMember($content));
    }

    public function setWebhook($url, $certificate = '', $dropPendingUpdates = false)
    {
        return TelegramApi::getResultOrThrow($this->api->setWebhook($url, $certificate, $dropPendingUpdates));
    }

    public function deleteWebhook($dropPendingUpdates = false)
    {
        return TelegramApi::getResultOrThrow($this->api->deleteWebhook($dropPendingUpdates));
    }

    public function getWebhookInfo()
    {
        return TelegramApi::getResultOrThrow($this->api->getWebhookInfo());
    }
}
