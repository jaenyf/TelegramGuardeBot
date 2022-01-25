<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Estimators;

interface TextEstimator
{
    /**
     * Retrieve the best estimation for a given text
     * @param string The text to estimate
     * @return string The text estimated label
     */
    public function estimate(string $text) : string;
}
