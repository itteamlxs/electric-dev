<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Security;
use App\Config\ErrorHandler;
use App\Config\Router;
use App\Config\RateLimiter;
use App\Controllers\ApiController;

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Europe/Madrid');

// Initialize
ErrorHandler::register();
Security::setHeaders();
RateLimiter::check();

// Router
$router = new Router();
$api = new ApiController();

// Routes
$router->get('/api/today', [$api, 'getToday']);
$router->get('/api/tomorrow', [$api, 'getTomorrow']);
$router->get('/api/hours', [$api, 'getHours']);
$router->get('/api/task/lavadora', fn() => $api->getTaskRecommendation('lavadora'));
$router->get('/api/task/secadora', fn() => $api->getTaskRecommendation('secadora'));
$router->get('/api/task/horno', fn() => $api->getTaskRecommendation('horno'));
$router->get('/api/task/lavavajillas', fn() => $api->getTaskRecommendation('lavavajillas'));

// Health check
$router->get('/api/health', function() use ($router) {
    $router->sendResponse([
        'status' => 'ok',
        'version' => '1.0.0',
        'timestamp' => date('c')
    ]);
});

// Dispatch
$router->dispatch();
