<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Log\Handler;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use TelegramGuardeBot\GuardeBot;

/**
 * Handler to log to the telegram debug log chat
 */
class TelegramChatHandler extends AbstractProcessingHandler
{

    private GuardeBot $bot;
    private int $telegramChatId;

    /**
     * @param GuardeBot  $bot The GuardeBot instance to use
     * @param int        $telegramChatId The chat id where to log
     */
    public function __construct(GuardeBot $bot, int $telegramChatId, $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->telegramChatId = $telegramChatId;
        $this->bot = $bot;
    }


    /**
     * {@inheritDoc}
     */
    protected function write(array $record): void
    {
        $this->bot->say($this->telegramChatId, (string) $record['formatted']);
    }
}
