<?php

namespace TelegramGuardeBot\Log;

use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use TelegramGuardeBot\GuardeBot;
use TelegramGuardeBot\Log\Handler\TelegramChatHandler;

/**
 * GuardeBot main logger utility Class.
 */
class GuardeBotLogger
{
    private static LoggerInterface $instance;
    private static ?bool $isInitialized;
    private static $bot;
    private static int $telegramLogChatId;

    public static function initialize(GuardeBot $bot, int $telegramLogChatId)
    {
        static::$bot = $bot;
        static::$telegramLogChatId = $telegramLogChatId;
        static::$isInitialized = true;
    }

    public static function getInstance(): LoggerInterface
    {
        if (isset(static::$instance)) {
            return static::$instance;
        }

        $logger = new Logger('GuardeBot');

        if (isset(static::$isInitialized) && static::$isInitialized === true) {
            $logger->pushHandler(new TelegramChatHandler(static::$bot, static::$telegramLogChatId));
        }

        $defaultHandler = new StreamHandler('logs/GuardeBot.log', Logger::DEBUG);
        $formatter = new LineFormatter(LineFormatter::SIMPLE_FORMAT, LineFormatter::SIMPLE_DATE);
        $formatter->includeStacktraces(true);
        $defaultHandler->setFormatter($formatter);
        $logger->pushHandler($defaultHandler);

        //Register global errors handler
        $errorHandler = new ErrorHandler($logger);
        $errorHandler->registerErrorHandler([], true);
        $errorHandler->registerExceptionHandler();
        $errorHandler->registerFatalHandler();

        return (static::$instance = $logger);
    }
}
