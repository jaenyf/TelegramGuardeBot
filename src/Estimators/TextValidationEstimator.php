<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Estimators;

interface TextValidationEstimator
{
    /**
     * Whether or not a given text is considered valid
     * @return bool
     */
    public function isValid(string $text): bool;
}
