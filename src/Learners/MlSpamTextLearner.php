<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Learners;

use TelegramGuardeBot\Learners\TextValidationLearner;

use TelegramGuardeBot\Helpers\TextHelper;

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;

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
            $text = TextHelper::normalize($text);

            file_put_contents("spams.learning.lst", json_encode($text).PHP_EOL, FILE_APPEND | LOCK_EX);

            $estimator = PersistentModel::load(new Filesystem('spamestimator.rbx'));

            $simpleDataset =  Labeled::build([$text], ["spam"]);
            $estimator->partial($simpleDataset);
            $estimator->save();
        }
    }
}
