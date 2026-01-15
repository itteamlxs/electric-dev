<?php

namespace App\Config;

class ApiKeyAuth
{
    private const API_KEYS_FILE = '/var/www/html/storage/api_keys.json';
    
    public static function validate(): bool
    {
        $apiKey = self::getApiKeyFromRequest();
        
        if (!$apiKey) {
            self::sendUnauthorized('API key required');
            return false;
        }
        
        if (!self::isValidKey($apiKey)) {
            Logger::warning("Invalid API key attempt", ['key' => substr($apiKey, 0, 8) . '...']);
            self::sendUnauthorized('Invalid API key');
            return false;
        }
        
        self::logKeyUsage($apiKey);
        return true;
    }
    
    private static function getApiKeyFromRequest(): ?string
    {
        $headers = [
            'HTTP_X_API_KEY',
            'HTTP_AUTHORIZATION'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $value = $_SERVER[$header];
                if (str_starts_with($value, 'Bearer ')) {
                    return substr($value, 7);
                }
                return $value;
            }
        }
        
        return $_GET['api_key'] ?? null;
    }
    
    private static function isValidKey(string $key): bool
    {
        if (!file_exists(self::API_KEYS_FILE)) {
            return false;
        }
        
        $keys = json_decode(file_get_contents(self::API_KEYS_FILE), true);
        
        foreach ($keys as $keyData) {
            if (hash_equals($keyData['key'], $key)) {
                if (isset($keyData['expires']) && time() > $keyData['expires']) {
                    return false;
                }
                return $keyData['active'] ?? true;
            }
        }
        
        return false;
    }
    
    private static function logKeyUsage(string $key): void
    {
        Logger::info("API key used", [
            'key_prefix' => substr($key, 0, 8),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ]);
    }
    
    private static function sendUnauthorized(string $message): void
    {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => $message,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    public static function generateKey(string $name, ?int $expiresIn = null): array
    {
        $key = bin2hex(random_bytes(32));
        $data = [
            'key' => $key,
            'name' => $name,
            'created' => time(),
            'active' => true
        ];
        
        if ($expiresIn) {
            $data['expires'] = time() + $expiresIn;
        }
        
        $keys = [];
        if (file_exists(self::API_KEYS_FILE)) {
            $keys = json_decode(file_get_contents(self::API_KEYS_FILE), true);
        }
        
        $keys[] = $data;
        
        $dir = dirname(self::API_KEYS_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents(self::API_KEYS_FILE, json_encode($keys, JSON_PRETTY_PRINT), LOCK_EX);
        
        return $data;
    }
}
