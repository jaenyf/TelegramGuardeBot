<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Validators;

use TelegramGuardeBot\Validators\TextValidator;

use Phpml\ModelManager;

class MlSpamTextValidator implements TextValidator
{
    /**
     * Validate a given text
     * @return bool
     */
    public function validate(string $text): bool
    {
        $modelManager = new ModelManager();
        $model = $modelManager->restoreFromFile('passes-ml-model.dat');
        $prediction = $model->predict([$text]);
        return $prediction[0] === "negatifs";
    }
}