<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Helpers;

class MarkdownHelper
{

    public static function escape(string $text)
    {
        return addcslashes($text, '_*][)(~`>#+-=|}{.!');
    }
}
