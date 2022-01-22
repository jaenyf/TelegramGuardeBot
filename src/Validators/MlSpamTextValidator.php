<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Validators;

use TelegramGuardeBot\Validators\TextValidator;

use TelegramGuardeBot\Helpers\TextHelper;

use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;

class MlSpamTextValidator implements TextValidator
{
    /**
     * Validate a given text
     * @return bool
     */
    public function validate(string $text): bool
    {
        $text = TextHelper::normalize($text);

        $estimator = PersistentModel::load(new Filesystem('spamestimator.rbx'));

        $dataset = new Unlabeled([$text]);
        $prediction = $estimator->predict($dataset);

        return $prediction[0] == "ham";
    }
}
