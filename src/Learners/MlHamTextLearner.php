<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Learners;

use TelegramGuardeBot\Learners\TextValidationLearner;

use TelegramGuardeBot\Helpers\TextHelper;
use TelegramGuardeBot\Helpers\MemoryHelper;

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;

class MlHamTextLearner implements TextValidationLearner
{
    /**
     * Validate a given text
     * @return bool
     */
    public function learn(string $text)
    {
        $text = TextHelper::normalize($text);

        file_put_contents("hams.learning.lst", json_encode($text).PHP_EOL, FILE_APPEND | LOCK_EX);

        MemoryHelper::storeAndSetMemoryLimit("-1");

        $estimator = PersistentModel::load(new Filesystem('spamestimator.rbx'));

        $simpleDataset =  Labeled::build([$text], ["ham"]);
        $estimator->partial($simpleDataset);
        $estimator->save();

        MemoryHelper::restoreMemoryLimit();
    }
}
