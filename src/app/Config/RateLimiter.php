<?php

namespace App\Config;

class RateLimiter
{
    private const CACHE_FILE = '/var/www/html/storage/rate_limit.json';
    
    private const LIMITS = [
        'default' => ['requests' => 100, 'window' => 3600],
        'health' => ['requests' => 1000, 'window' => 3600],
        'zones' => ['requests' => 200, 'window' => 3600],
        'today' => ['requests' => 500, 'window' => 3600],
        'tomorrow' => ['requests' => 500, 'window' => 3600],
        'hours' => ['requests' => 500, 'window' => 3600],
        'task' => ['requests' => 300, 'window' => 3600]
    ];
    
    public static function check(): bool
    {
        $ip = self::getClientIp();
        $endpoint = self::getEndpoint();
        $now = time();
        
        $limit = self::LIMITS[$endpoint] ?? self::LIMITS['default'];
        
        $data = self::loadData();
        $data = array_filter($data, fn($record) => isset($record['expires']) && $record['expires'] > $now);
        
        $key = $ip . ':' . $endpoint;
        $requests = array_filter($data, fn($record) => isset($record['key']) && $record['key'] === $key);
        
        if (count($requests) >= $limit['requests']) {
            self::sendTooManyRequests($limit['window']);
            return false;
        }
        
        $data[] = [
            'key' => $key,
            'ip' => $ip,
            'endpoint' => $endpoint,
            'time' => $now,
            'expires' => $now + $limit['window']
        ];
        
        self::saveData($data);
        
        return true;
    }
    
    private static function getEndpoint(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        
        if (preg_match('#^/api/(health|zones|today|tomorrow|hours)#', $uri, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('#^/api/task/#', $uri)) {
            return 'task';
        }
        
        return 'default';
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
        $data = json_decode($content, true);
        
        if (!is_array($data)) {
            return [];
        }
        
        return $data;
    }
    
    private static function saveData(array $data): void
    {
        $dir = dirname(self::CACHE_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        
        file_put_contents(self::CACHE_FILE, json_encode($data), LOCK_EX);
    }
    
    private static function sendTooManyRequests(int $retryAfter): void
    {
        http_response_code(429);
        header('Content-Type: application/json; charset=utf-8');
        header("Retry-After: $retryAfter");
        echo json_encode([
            'success' => false,
            'error' => 'Too many requests. Try again later.',
            'retry_after' => $retryAfter,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
