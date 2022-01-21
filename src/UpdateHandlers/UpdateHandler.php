<?php

declare(strict_types=1);

namespace TelegramGuardeBot\UpdateHandlers;

/**
 * Handle a Telegram update
 */
abstract class UpdateHandler
{
    public abstract function handle($update, $additionalData = null);
}
