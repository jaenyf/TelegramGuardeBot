<?php

declare(strict_types=1);

namespace TelegramGuardeBot\Estimators;

use TelegramGuardeBot\App;
use TelegramGuardeBot\Estimators\TextValidationEstimator;
use TelegramGuardeBot\Helpers\TextHelper;
use TelegramGuardeBot\Estimators\MlLanguageTextEstimator;

use Matriphe\ISO639\ISO639;

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
use Rubix\ML\Transformers\StopWordFilter;

use voku\helper\StopWords;


class MlSpamTextValidationEstimator implements TextValidationEstimator
{
    private const estimatorFileName = 'data/{%1}.spamestimator.rbx';

    public static function getEstimatorFileName(string $languageCode) : string
    {
        return str_replace("{%1}", $languageCode, self::estimatorFileName);
    }

    /**
     * Validate a given text
     * @return bool Whether or not the text is valid (ham)
     */
    public function isValid(string $text): bool
    {
        $text = TextHelper::normalize($text);

        //For now we discard text with less than {$minWordsForSpamEstimation} words, considering it as valid, as it easily triggers false positives with our current estimator state
        $minWordsForSpamEstimation = 4;
        $wordsCount = count(preg_split("/[\s,]+/", $text, $minWordsForSpamEstimation, PREG_SPLIT_NO_EMPTY));
        if($wordsCount < $minWordsForSpamEstimation)
        {
            return true;
        }

        $languageEstimator = App::getInstance()->getDIContainer()->get(MlLanguageTextEstimator::class);
        $languageName = $languageEstimator->estimate($text);

        $languageCode = (new ISO639())->code1ByLanguage($languageName);

        $estimatorFilename = self::getEstimatorFileName($languageCode);
        if(!file_exists($estimatorFilename))
        {
            App::getInstance()->getLogger()->info("Could not load spam estimator file", [$languageCode, $languageName, $text]);
            return true;
        }

        $estimator = PersistentModel::load(new Filesystem($estimatorFilename));

        $dataset = new Unlabeled([$text]);
        $prediction = $estimator->predict($dataset);

        return $prediction[0] == "ham";
    }

    /**
     * Create the ML estimator file
     * @param string the iso639-2 language code
     */
    public static function createEstimatorFile(string $languageCode) : void
    {
        $estimator = new PersistentModel(
            new Pipeline([
                new StopWordFilter((new StopWords())->getStopWordsFromLanguage($languageCode)),
                //FIX
                //new TextNormalizer(), //Causes Uncaught Rubix\ML\Exceptions\RuntimeException: Cannot create vocabulary from tokens given the document frequency constraints on column 0.
                new WordCountVectorizer(1000000, 0.005, 0.995, new NGram(1, 2)),
                new BM25Transformer(),
                new ZScaleStandardizer()
            ],
            new GaussianNB()),
            new Filesystem(MlSpamTextValidationEstimator::getEstimatorFileName($languageCode), false)
        );

        $estimator->save();
    }
}
