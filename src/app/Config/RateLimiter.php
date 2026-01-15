<?php

namespace App\Config;

class RateLimiter
{
    private const MAX_REQUESTS = 100;
    private const TIME_WINDOW = 3600; // 1 hora
    private const CACHE_FILE = '/var/www/html/storage/rate_limit.json';
    
    public static function check(): bool
    {
        $ip = self::getClientIp();
        $now = time();
        
        $data = self::loadData();
        
        // Limpiar registros antiguos
        $data = array_filter($data, fn($record) => $record['expires'] > $now);
        
        // Contar requests del IP
        $ipRequests = array_filter($data, fn($record) => $record['ip'] === $ip);
        
        if (count($ipRequests) >= self::MAX_REQUESTS) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'error' => 'Too many requests. Try again later.',
                'timestamp' => date('c')
            ]);
            exit;
        }
        
        // Registrar request
        $data[] = [
            'ip' => $ip,
            'time' => $now,
            'expires' => $now + self::TIME_WINDOW
        ];
        
        self::saveData($data);
        
        return true;
    }
    
    private static function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
            }
        }
        
        return '0.0.0.0';
    }
    
    private static function loadData(): array
    {
        if (!file_exists(self::CACHE_FILE)) {
            return [];
        }
        
        $content = file_get_contents(self::CACHE_FILE);
        return json_decode($content, true) ?: [];
    }
    
    private static function saveData(array $data): void
    {
        $dir = dirname(self::CACHE_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents(self::CACHE_FILE, json_encode($data), LOCK_EX);
    }
}
