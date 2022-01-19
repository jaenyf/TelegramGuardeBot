<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Actions;

abstract class MessageAction
{
    public abstract function act($message) : void;
}
