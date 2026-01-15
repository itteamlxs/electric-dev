<?php

namespace App\Config;

class Logger
{
    private static array $levels = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4
    ];
    
    public static function debug(string $message, array $context = []): void
    {
        self::log('debug', $message, $context);
    }
    
    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }
    
    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }
    
    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }
    
    public static function critical(string $message, array $context = []): void
    {
        self::log('critical', $message, $context);
    }
    
    private static function log(string $level, string $message, array $context): void
    {
        $configLevel = strtolower($_ENV['LOG_LEVEL'] ?? 'info');
        
        if (self::$levels[$level] < self::$levels[$configLevel]) {
            return;
        }
        
        $logPath = $_ENV['LOG_PATH'] ?? '/var/www/html/storage/logs';
        $filename = $logPath . '/app-' . date('Y-m-d') . '.log';
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
        
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }
        
        file_put_contents($filename, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
