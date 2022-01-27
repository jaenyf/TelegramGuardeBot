<?php

declare(strict_types=1);

namespace TelegramGuardeBot;

use TelegramGuardeBot\AppConfig;
use TelegramGuardeBot\GuardeBot;
use TelegramGuardeBot\Log\GuardeBotLogger;
use TelegramGuardeBot\DependenciesInitialization;
use Psr\Log\LoggerInterface;
use DI\Container;

class App
{


    private static $instance;

    private AppConfig $appConfig;
    private Container $diContainer;

    private function __construct(AppConfig $appConfig)
    {
        if(!isset($appConfig))
        {
            throw new \InvalidArgumentException("appConfig is not set");
        }

        $this->appConfig = $appConfig;
    }


    public static function initialize($configFileName = null, ?Container $diContainer = null)
    {
        if (isset(self::$instance))
        {
            throw new \ErrorException('Already initialized');
        }

        if (!isset($configFileName))
        {
            $configFileName = AppConfig::defaultConfigFileName;
        }

        $diContainer = $diContainer ?? DependenciesInitialization::InitializeContainer($configFileName);

        self::$instance = new App($diContainer->get('appConfig'));
        self::$instance->diContainer = $diContainer;

        GuardeBotLogger::initialize(self::$instance->getDIContainer()->get('bot'), self::$instance->appConfig->logChatId);
        GuardeBotLogger::getInstance(); //register error handlers
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


    public function getDIContainer() : Container
    {
        return $this->diContainer;
    }

    /**
     * Shorthand for getDIContainer()->get('logger')
     */
    public function getLogger() : LoggerInterface
    {
        return $this->getDIContainer()->get('logger');
    }

    /**
     * Shorthand for getDIContainer()->get('bot')
     */
    public function getBot(): GuardeBot
    {
        return $this->getDIContainer()->get('bot');
    }
}
