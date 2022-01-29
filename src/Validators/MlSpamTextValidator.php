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
     * @return bool Whether or not the text is valid (ham)
     */
    public function validate(string $text): bool
    {
        $text = TextHelper::normalize($text);

        //For now we discard text with less than {$minWordsForSpamEstimation} words, considering it as valid, as it easily triggers false positives with our current estimator state
        $minWordsForSpamEstimation = 4;
        $wordsCount = count(preg_split("/[\s,]+/", $text, $minWordsForSpamEstimation, PREG_SPLIT_NO_EMPTY));
        if($wordsCount < $minWordsForSpamEstimation)
        {
            return true;
        }

        if(file_exists('spamestimator.rbx'))
        {
            $estimator = PersistentModel::load(new Filesystem('spamestimator.rbx'));

            $dataset = new Unlabeled([$text]);
            $prediction = $estimator->predict($dataset);
    
            return $prediction[0] == "ham";
        }
        
        return true;
    }
}
