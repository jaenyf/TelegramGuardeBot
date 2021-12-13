<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Validators;

interface TextValidator
{
    /**
     * Validate a given text
     * @return bool
     */
    public function validate(string $text): bool;
}