<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Services\PriceImporter;
use App\Config\ErrorHandler;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Europe/Madrid');
ErrorHandler::register();

// Obtener fecha del argumento o usar hoy
$date = $argv[1] ?? date('Y-m-d');

echo "Importing prices for date: $date\n";

$importer = new PriceImporter();
$success = $importer->importForDate($date);

if ($success) {
    echo "✓ Import completed successfully\n";
    exit(0);
} else {
    echo "✗ Import failed\n";
    exit(1);
}
