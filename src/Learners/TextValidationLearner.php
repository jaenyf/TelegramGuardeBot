<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Learners;

interface TextValidationLearner
{
    /**
     * Validate a given text
     * @return bool
     */
    public function learn(string $text);
}
