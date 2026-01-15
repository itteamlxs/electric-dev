<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$date = '2026-01-15';
$startDate = $date . 'T00:00:00';
$endDate = $date . 'T23:59:59';

$url = 'https://api.esios.ree.es/indicators/1001?' . http_build_query([
    'start_date' => $startDate,
    'end_date' => $endDate
]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'x-api-key: ' . $_ENV['ESIOS_API_TOKEN']
    ]
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

echo "Total values: " . count($data['indicator']['values']) . "\n\n";
echo "First 3 items:\n";
print_r(array_slice($data['indicator']['values'], 0, 3));
