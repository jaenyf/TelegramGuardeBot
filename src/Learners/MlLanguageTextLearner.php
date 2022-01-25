<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Learners;

use TelegramGuardeBot\Learners\TextEstimatorLearner;
use TelegramGuardeBot\Helpers\TextHelper;

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Pipeline;
use Rubix\ML\Transformers\TextNormalizer;
use Rubix\ML\Transformers\WordCountVectorizer;
use Rubix\ML\Tokenizers\NGram;
use Rubix\ML\Transformers\ZScaleStandardizer;
use Rubix\ML\Classifiers\GaussianNB;
use Rubix\ML\Transformers\BM25Transformer;

class MlLanguageTextLearner implements TextEstimatorLearner
{
    public const estimatorFileName = 'data/languageestimator.rbx';

    /**
     * Validate a given text
     */
    public function learn(string $languageName, string $text)
    {
        $languageName = strtolower($languageName);
        $text = TextHelper::toSingleNormalizedLine($text);

        file_put_contents("data/languageestimator.csv", $languageName.','.$text.PHP_EOL, FILE_APPEND | LOCK_EX);

        if(!file_exists(self::estimatorFileName))
        {
            self::createEstimatorFile();
        }

        $estimator = PersistentModel::load(new Filesystem(self::estimatorFileName));

        $simpleDataset =  Labeled::build([$text], [$languageName]);
        $estimator->partial($simpleDataset);
        $estimator->save();
    }

    public static function createEstimatorFile() : void
    {
        $estimator = new PersistentModel(
            new Pipeline([
                new TextNormalizer(),
                new WordCountVectorizer(1000000, 0.05, 0.95, new NGram(1, 2)),
                new BM25Transformer(),
                new ZScaleStandardizer()
            ],
            new GaussianNB()),
            new Filesystem(self::estimatorFileName, false)
        );

        $estimator->save();
    }
}
