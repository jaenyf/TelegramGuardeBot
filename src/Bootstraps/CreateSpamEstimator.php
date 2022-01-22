<?php
declare(strict_types=1);

namespace TelegramGuardeBot\Tests\Learners;

ini_set('memory_limit', '-1');

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Pipeline;
use Rubix\ML\Transformers\TextNormalizer;
use Rubix\ML\Transformers\WordCountVectorizer;
use Rubix\ML\Transformers\TfIdfTransformer;
use Rubix\ML\Transformers\ZScaleStandardizer;
use Rubix\ML\Tokenizers\NGram;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\CrossValidation\Reports\AggregateReport;
use Rubix\ML\CrossValidation\Reports\ConfusionMatrix;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Kernels\Distance\Manhattan;



/**
 * Prepare the samples and labels
 */
$samples = $labels = [];
$handle = fopen("hams.learning.lst", "r");
while (($line = fgets($handle)) !== false) {
    // process the line read.
    $labels[] = "ham";
    $samples[] = [json_decode($line)];
}
fclose($handle);

$handle = fopen("spams.learning.lst", "r");
while (($line = fgets($handle)) !== false) {
    // process the line read.
    $labels[] = "spam";
    $samples[] = [json_decode($line)];
}
fclose($handle);



/**
 * Create the rbx file
 */
$dataset = new Labeled($samples, $labels);
$estimator = new PersistentModel(
    new Pipeline([
        new TextNormalizer(),
        new WordCountVectorizer(10000, 0.0, 1.0, new NGram(1, 2)),
        new TfIdfTransformer(),
        new ZScaleStandardizer(),
    ], new KNearestNeighbors(3, false, new Manhattan())
),
    new Filesystem('spamestimator.rbx', false)
);

$estimator->train($dataset);
$estimator->save();



/**
 * Create the report
 */
$dataset = Labeled::build($samples, $labels)->randomize()->take(38);
$estimator = PersistentModel::load(new Filesystem('spamestimator.rbx'));
$predictions = $estimator->predict($dataset);
$report = new AggregateReport([
    new MulticlassBreakdown(),
    new ConfusionMatrix(),
]);
$results = $report->generate($predictions, $dataset->labels());
$results->toJSON()->saveTo(new Filesystem('spamestimator.report.json'));
