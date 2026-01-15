<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Database;
use App\Config\Logger;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $db = Database::getConnection();
    
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    
    $db->exec($sql);
    
    Logger::info("Database migration completed successfully");
    echo "✓ Migration completed\n";
    
} catch (\Exception $e) {
    Logger::error("Migration failed: " . $e->getMessage());
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
