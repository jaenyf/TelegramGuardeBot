<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Estimators;

use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Pipeline;
use Rubix\ML\Transformers\TextNormalizer;
use Rubix\ML\Transformers\WordCountVectorizer;
use Rubix\ML\Tokenizers\NGram;
use Rubix\ML\Transformers\ZScaleStandardizer;
use Rubix\ML\Classifiers\GaussianNB;
use Rubix\ML\Transformers\BM25Transformer;

use TelegramGuardeBot\Estimators\TextEstimator;
use TelegramGuardeBot\Helpers\TextHelper;

class MlLanguageTextEstimator implements TextEstimator
{
    public const estimatorFileName = 'data/languageestimator.rbx';

    public function estimate(string $text): string
    {
        $text = TextHelper::normalize($text);

        if(!file_exists(self::estimatorFileName))
        {
            self::createEstimatorFile();
        }

        $estimator = PersistentModel::load(new Filesystem(self::estimatorFileName));

        $dataset = new Unlabeled([$text]);
        $prediction = $estimator->predict($dataset);

        return $prediction[0];
    }

    public static function createEstimatorFile() : void
    {
        $estimator = new PersistentModel(
            new Pipeline([
                new TextNormalizer(),
                new WordCountVectorizer(1100400*40, 0.001, 0.999, new NGram(1, 2)),
                new BM25Transformer(),
                new ZScaleStandardizer()
            ],
            new GaussianNB()),
            new Filesystem(self::estimatorFileName, false)
        );

        $estimator->save();
    }
}
