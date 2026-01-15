<?php

namespace App\Config;

class Security
{
    public static function setHeaders(): void
    {
        $allowedOrigins = array_filter(explode(',', $_ENV['ALLOWED_ORIGINS'] ?? ''));
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array($origin, $allowedOrigins, true)) {
            header("Access-Control-Allow-Origin: $origin");
        }
        
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
        header('Access-Control-Max-Age: 86400');
        
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header('X-Permitted-Cross-Domain-Policies: none');
        
        if ($_ENV['APP_ENV'] === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
    
    public static function validateInput(string $input, string $type = 'string'): bool
    {
        switch ($type) {
            case 'date':
                return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $input);
            case 'geo_id':
                return is_numeric($input) && in_array((int)$input, [8741, 8742, 8743, 8744, 8745], true);
            case 'task_code':
                return in_array($input, ['lavadora', 'secadora', 'horno', 'lavavajillas'], true);
            default:
                return is_string($input) && strlen($input) < 255;
        }
    }
    
    public static function sanitizeOutput(string $message): string
    {
        return htmlspecialchars($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
