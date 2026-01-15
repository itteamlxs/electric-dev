<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Security;
use App\Config\ErrorHandler;

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Europe/Madrid');

// Initialize
ErrorHandler::register();
Security::setHeaders();

// Router básico (temporal)
echo json_encode([
    'success' => true,
    'message' => 'API initialized',
    'version' => '1.0.0'
]);
