<?php

/**
 * This script train a ML language estimator model by parsing a dataset file containing lines in the form of : LANGUAGE_NAME,TEXT
 * @param file - (required) The csv file that will be parsed
 */

declare(strict_types=1);

namespace TelegramGuardeBot\Bootstraps\Learners;

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

use TelegramGuardeBot\Estimators\MlLanguageTextEstimator;

$languageDetectionCsvFilename = '';
if (isset($argc)) {
    if($argc != 2)
    {
        throw new \ErrorException("Wrong number of arguments !");
    }

    $languageDetectionCsvFilename = $argv[1];

    if(!file_exists($languageDetectionCsvFilename))
    {
        throw new \ErrorException("File does not exits : \"".$languageDetectionCsvFilename."\"");
    }
}
else {
	throw new \ErrorException("Can not access command line arguments !");
}

/**
 * Prepare the samples and labels
 */
$samples = $labels = $uniquesLabels = [];
$handle = fopen($languageDetectionCsvFilename, "r");
$csvLinePregParse = '/(\w+),(.*)/';
$lineCount = 0;
$failedCsvMatches = 0;

if(file_exists($languageDetectionCsvFilename.".failed"))
{
    unlink($languageDetectionCsvFilename.".failed");
}

while (($line = fgets($handle)) !== false) {
    // process the line read.
    ++$lineCount;
    if(1 === preg_match($csvLinePregParse, $line, $matches))
    {
        $language = strtolower($matches[1]);
        if($language === 'language')
        {
            //this is a header
            continue;
        }
        $text = $matches[2];

        if(!in_array($language, $uniquesLabels))
        {
            $uniquesLabels[] = $language;
        }

        $labels[] = $language;
        $samples[] = $text;
    }
    else
    {
        ++$failedCsvMatches;
        echo('Failed matching csv line at line '.$lineCount.PHP_EOL);
        file_put_contents($languageDetectionCsvFilename.".failed", $line, FILE_APPEND);
    }
}
echo(PHP_EOL.PHP_EOL.'Total failed CSV lines matches :'.$failedCsvMatches.PHP_EOL);
fclose($handle);

echo 'Processed following '.count($uniquesLabels).' languages : ' . implode(', ', $uniquesLabels) . PHP_EOL;

/**
 * Create the rbx file
 */
$dataset = new Labeled($samples, $labels);

MlLanguageTextEstimator::createEstimatorFile();
$estimator = PersistentModel::load(new Filesystem(MlLanguageTextEstimator::estimatorFileName));
$estimator->train($dataset);
$estimator->save();


/**
 * Create the report
 */
$dataset = Labeled::build($samples, $labels)->randomize()->take(38);
$estimator = PersistentModel::load(new Filesystem(MlLanguageTextEstimator::estimatorFileName));
$predictions = $estimator->predict($dataset);
$report = new AggregateReport([
    new MulticlassBreakdown(),
    new ConfusionMatrix(),
]);
$results = $report->generate($predictions, $dataset->labels());
$results->toJSON()->saveTo(new Filesystem(MlLanguageTextEstimator::estimatorFileName.'.report.json'));

echo("All done.".PHP_EOL);
