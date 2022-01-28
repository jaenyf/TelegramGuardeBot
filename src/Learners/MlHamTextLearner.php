<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Learners;

use TelegramGuardeBot\App;
use TelegramGuardeBot\Estimators\MlSpamTextValidationEstimator;
use TelegramGuardeBot\Learners\TextValidationLearner;
use TelegramGuardeBot\Helpers\TextHelper;

use TelegramGuardeBot\Estimators\MlLanguageTextEstimator;

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;

use Matriphe\ISO639\ISO639;

class MlHamTextLearner implements TextValidationLearner
{
    /**
     * Validate a given text
     */
    public function learn(string $text)
    {
        $languageEstimator = App::getInstance()->getDIContainer()->get(MlLanguageTextEstimator::class);
        $languageName = $languageEstimator->estimate($text);
        $languageCode = (new ISO639())->code1ByLanguage($languageName);

        if($languageCode === '')
        {
            throw new \ErrorException("Unsupported language iso code");
        }

        $text = TextHelper::normalize($text);

        file_put_contents("data/'.$languageCode.'.hams.learning.lst", json_encode($text).PHP_EOL, FILE_APPEND | LOCK_EX);

        if(!file_exists(MlSpamTextValidationEstimator::getEstimatorFileName($languageCode)))
        {
            MlSpamTextValidationEstimator::createEstimatorFile($languageCode);
        }

        $estimator = PersistentModel::load(new Filesystem($languageCode.'.'.MlSpamTextValidationEstimator::getEstimatorFileName($languageCode)));

        $simpleDataset =  Labeled::build([$text], ["ham"]);
        $estimator->partial($simpleDataset);
        $estimator->save();
    }
}
