<?php

declare(strict_types=1);

namespace TelegramGuardeBot;

use TelegramGuardeBot\GuardeBot;
use TelegramGuardeBot\TelegramApi;
use TelegramGuardeBot\Log\GuardeBotLogger;
use TelegramGuardeBot\DependenciesInitialization;
use Psr\Log\LoggerInterface;

class App
{
    private const DefaultConfigFileName = 'app.config';

    private static $instance;

    public string $envName;
    public string $botToken;
    public int $logChatId;
    public string $locale;
    public bool $enableApiLogging;
    public array $messagesActions;
    public bool $enableNewMemberValidation;
    public int $newMemberValidationTimeout;
    private GuardeBot $bot;
    private $diContainer;

    private function __construct(string $configFileName)
    {
        if (!file_exists($configFileName))
        {
            throw new \ErrorException('Configuration file not found');
        }

        $config = json_decode(self::stripComments(file_get_contents($configFileName)), false);

        $this->envName = $config->envName;
        $this->botToken = $config->botToken;
        $this->logChatId = $config->logChatId;
        $this->locale = strtoupper($config->locale);
        $this->enableApiLogging = $config->enableApiLogging;
        $this->messagesActions = $config->messagesActions;
        $this->enableNewMemberValidation = $config->enableNewMemberValidation ?? false;
        $this->newMemberValidationTimeout = $config->newMemberValidationTimeout;


        if (empty($this->locale))
        {
            throw new \ErrorException('Missing locale');
        }

        switch ($this->locale)
        {
            case 'FR':
            case 'EN':
                break;
            default:
                throw new \ErrorException('Unsupported locale');
        }
        require_once fromAppSource('/i18n/GuardeBotMessages_' . $this->locale . '.php');

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

    public static function initialize($configFileName = null, $diContainer = null)
    {
        if (isset(self::$instance))
        {
            throw new \ErrorException('Already initialized');
        }

        if (!isset($configFileName))
        {
            $configFileName = App::DefaultConfigFileName;
        }

        self::$instance = new App($configFileName);

        self::$instance->diContainer = $diContainer ?? DependenciesInitialization::InitializeContainer(self::$instance->envName);

    }

    public static function isInitialized() : bool
    {
        return isset(self::$instance);
    }

    public static function dispose()
    {
        self::$instance = null;
    }

    public static function getInstance(): App
    {
        if (!self::isInitialized())
        {
            throw new \ErrorException('Not initialized');
        }

        return self::$instance;
    }


    public function getLogger() : LoggerInterface
    {
        return $this->diContainer->get('logger');
    }

    public function getBot(): GuardeBot
    {
        return $this->bot;
    }
}
