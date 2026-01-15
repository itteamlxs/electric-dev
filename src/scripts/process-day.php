<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Services\PriceImporter;
use App\Services\HourClassifier;
use App\Services\RecommendationGenerator;
use App\Config\ErrorHandler;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Europe/Madrid');
ErrorHandler::register();

$date = $argv[1] ?? date('Y-m-d');

echo "Processing date: $date\n";
echo "Step 1/3: Importing prices...\n";

$importer = new PriceImporter();
if (!$importer->importForDate($date)) {
    echo "✗ Import failed\n";
    exit(1);
}

echo "✓ Prices imported\n";
echo "Step 2/3: Classifying hours...\n";

$classifier = new HourClassifier();
if (!$classifier->classifyDate($date)) {
    echo "✗ Classification failed\n";
    exit(1);
}

echo "✓ Hours classified\n";
echo "Step 3/3: Generating recommendations...\n";

$generator = new RecommendationGenerator();
if (!$generator->generateForDate($date)) {
    echo "✗ Recommendation generation failed\n";
    exit(1);
}

echo "✓ Recommendations generated\n";
echo "✓ Processing completed successfully\n";
exit(0);
