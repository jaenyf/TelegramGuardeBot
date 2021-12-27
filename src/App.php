<?php

declare(strict_types=1);

namespace TelegramGuardeBot;

use TelegramGuardeBot\GuardeBot;
use TelegramGuardeBot\TelegramApi;
use TelegramGuardeBot\Log\GuardeBotLogger;

class App
{
    private const DefaultConfigFileName = 'app.config';

    private static App $instance;

    public string $botToken;
    public int $logChatId;
    public string $locale;
    public bool $enableApiLogging;
    private GuardeBot $bot;

    private function __construct(string $configFileName)
    {
        if (!file_exists($configFileName)) {
            throw new \ErrorException('Configuration file not found');
        }

        $config = json_decode(self::stripComments(file_get_contents($configFileName)), false);

        $this->botToken = $config->botToken;
        $this->logChatId = $config->logChatId;
        $this->locale = strtoupper($config->locale);
        $this->enableApiLogging = $config->enableApiLogging;


        if (empty($this->locale)) {
            throw new \ErrorException('Missing locale');
        }

        switch ($this->locale) {
            case 'FR':
            case 'EN':
                break;
            default:
                throw new \ErrorException('Unsupported locale');
        }
        require_once('src/i18n/GuardeBotMessages_' . $this->locale . '.php');

        $this->bot = new GuardeBot(new TelegramApi($this->botToken, $this->enableApiLogging), $this->locale, $this->logChatId);

        GuardeBotLogger::initialize($this->bot, $this->logChatId);
        GuardeBotLogger::getInstance(); //register error handlers
    }

    /**
     * From https://stackoverflow.com/a/10252511/319266
     * @param string $str
     * @return string
     */
    private static function stripComments($text)
    {
        return preg_replace('![ \t]*//.*[ \t]*[\r\n]!', '', $text);
    }

    public static function initialize($configFileName = null)
    {
        if (isset(self::$instance)) {
            throw new \ErrorException('Already initialized');
        }

        if (!isset($configFileName)) {
            $configFileName = App::DefaultConfigFileName;
        }

        self::$instance = new App($configFileName);
    }

    public static function getInstance(): App
    {
        if (!isset(self::$instance)) {
            throw new \ErrorException('Not initialized');
        }

        return self::$instance;
    }

    public function getBot(): GuardeBot
    {
        return $this->bot;
    }
}