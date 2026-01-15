<?php

namespace App\Controllers;

class BaseController
{
    protected function jsonResponse(array $data, int $code = 200): void
    {
        http_response_code($code);
        echo json_encode([
            'success' => $code >= 200 && $code < 300,
            'data' => $data,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    protected function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    protected function getQueryParam(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
    
    protected function validateDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
