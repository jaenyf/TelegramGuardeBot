<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Learners;

use TelegramGuardeBot\Learners\TextValidationLearner;

use Phpml\ModelManager;

class MlSpamTextLearner implements TextValidationLearner
{
    /**
     * Validate a given text
     * @return bool
     */
    public function learn(string $text, bool $isValid)
    {
        $modelManager = new ModelManager();
        $model = $modelManager->restoreFromFile('passes-ml-model.dat');
        //TODO: phpai/php-ml multiple calls to pipeline->train generate errors
        //FIX: see issue #6 (https://gitlab.com/php-ai/php-ml/-/issues/6)
        //$model->train([$text], [$isValid ? 'negatifs' : 'positifs']);
        $modelManager->saveToFile($model, 'passes-ml-model.dat');
    }
}
