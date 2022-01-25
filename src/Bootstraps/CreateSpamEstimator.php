<?php

/**
 * This script is intended to setup the spam estimators for various languages.
 * It can parse an optional dataset file containing ham lines for various languages in the form of : LANGUAGE_NAME,TEXT
 * It will process additional data csv files (if found) named "data/LANG_CODE.hams.learning.lst" and "data/LANG_CODE.spams.learning.lst"
 * @param file - (optional) The csv ham file that will be parsed
 */

declare(strict_types=1);

namespace TelegramGuardeBot\Tests\Learners;

ini_set('display_errors', "1");
error_reporting(E_ALL);

require_once 'src/Requires.php';
require_once fromAppRoot('/vendor/autoload.php');

ini_set('memory_limit', '-1');

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\CrossValidation\Reports\AggregateReport;
use Rubix\ML\CrossValidation\Reports\ConfusionMatrix;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;

use TelegramGuardeBot\Estimators\MlSpamTextValidationEstimator;

use voku\helper\StopWords;
use voku\helper\StopWordsLanguageNotExists;
use Matriphe\ISO639\ISO639;


$hamCsvFilename = '';
if (isset($argc)) {
    if($argc > 2)
    {
        throw new \ErrorException("Wrong number of arguments !");
    }

    $hamCsvFilename = $argc === 2 ? $argv[1] : '';

    if(!empty($hamCsvFilename))
    {
        if(!file_exists($hamCsvFilename))
        {
            throw new \ErrorException("File does not exits : \"".$hamCsvFilename."\"");
        }
    }
}
else {
	throw new \ErrorException("Can not access command line arguments !");
}

//throw new \ErrorException("STOP");

$stopWords = new StopWords();
$iso639 = new ISO639();
$allIsoLanguages = $iso639->allLanguages();

foreach($allIsoLanguages as $language)
{
    $anyDataForThisLanguage = false;
    $iso639_2 = $language[0];
    $languageStopWords = [];
    $languageEnglishName = $language[4];

    try
    {
        $languageStopWords = $stopWords->getStopWordsFromLanguage($iso639_2);
    }
    catch(StopWordsLanguageNotExists $e)
    {
        continue;
    }

    echo('Processing '.$languageEnglishName.' ...'.PHP_EOL);

    /**
     * Prepare the samples and labels
     */
    $samples = $labels = [];

    $hamsFilename = "data/".$iso639_2.".hams.learning.lst";
    if(file_exists($hamsFilename))
    {
        $handle = fopen($hamsFilename, "r");
        while (($line = fgets($handle)) !== false) {
            // process the line read.
            $labels[] = "ham";
            $samples[] = [json_decode($line, false, 512, JSON_THROW_ON_ERROR)];
            $anyDataForThisLanguage = true;
        }
        fclose($handle);
    }

    $spamsFilename = "data/".$iso639_2.".spams.learning.lst";
    if(file_exists($spamsFilename))
    {
        $handle = fopen($spamsFilename, "r");
        while (($line = fgets($handle)) !== false) {
            // process the line read.
            $labels[] = "spam";
            $samples[] = [json_decode($line, false, 512, JSON_THROW_ON_ERROR)];
            $anyDataForThisLanguage = true;
        }
        fclose($handle);
    }


    //process ham file searching for current language ...
    if(!empty($hamCsvFilename))
    {
        if(file_exists($hamCsvFilename.".failed"))
        {
            unlink($hamCsvFilename.".failed");
        }

        echo('Process ham dataset searching for ('.$languageEnglishName.')...'.PHP_EOL);
        $csvLinePregParse = '/(\w+),(.*)/';
        $handle = fopen($hamCsvFilename, "r");
        $lineCount = 0;
        while (($line = fgets($handle)) !== false)
        {
            ++$lineCount;
            if(1 === preg_match($csvLinePregParse, $line, $matches))
            {
                $csvLineLanguage = strtolower($matches[1]);

                if($csvLineLanguage !== strtolower($languageEnglishName))
                {
                    continue;
                }

                $text = $matches[2];

                $labels[] = "ham";
                $samples[] = $text;
                $anyDataForThisLanguage = true;
            }
            else
            {
                ++$failedCsvMatches;
                echo('Failed matching csv line at line '.$lineCount.PHP_EOL);
                file_put_contents($hamCsvFilename.".failed", $line, FILE_APPEND);
            }
        }
        fclose($handle);
    }

    if(!$anyDataForThisLanguage)
    {
        continue;
    }

    /**
     * Create the rbx file
     */
    $dataset = new Labeled($samples, $labels);

    MlSpamTextValidationEstimator::createEstimatorFile($iso639_2);
    $estimator = PersistentModel::load(new Filesystem(MlSpamTextValidationEstimator::getEstimatorFileName($iso639_2)));

    $estimator->train($dataset);
    $estimator->save();

    /**
     * Create the report
     */
    $dataset = Labeled::build($samples, $labels)->randomize()->take(38);
    $estimator = PersistentModel::load(new Filesystem(MlSpamTextValidationEstimator::getEstimatorFileName($iso639_2)));
    $predictions = $estimator->predict($dataset);
    $report = new AggregateReport([
        new MulticlassBreakdown(),
        new ConfusionMatrix(),
    ]);
    $results = $report->generate($predictions, $dataset->labels());
    $results->toJSON()->saveTo(new Filesystem(MlSpamTextValidationEstimator::getEstimatorFileName($iso639_2).'.report.json'));
}

echo("All done.".PHP_EOL);
