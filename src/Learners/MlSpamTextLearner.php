<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Learners;

use TelegramGuardeBot\Learners\TextValidationLearner;

class MlSpamTextLearner implements TextValidationLearner
{
    /**
     * Validate a given text
     * @return bool
     */
    public function learn(string $text, bool $isValid)
    {

        if(!$isValid)
        {
            file_put_contents("spams.learning.lst", json_encode($text).PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }
}
