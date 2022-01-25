<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Learners;

interface TextEstimatorLearner
{
    /**
     * Learn that a given text is to be estimated with given label
     */
    public function learn(string $label, string $text);
}
