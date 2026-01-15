<?php

namespace App\Config;

class ErrorHandler
{
    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    public static function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (!(error_reporting() & $level)) {
            return false;
        }
        
        Logger::error("PHP Error: $message in $file:$line", [
            'level' => $level,
            'file' => $file,
            'line' => $line
        ]);
        
        if ($_ENV['APP_ENV'] === 'production') {
            self::sendJsonError('Internal server error', 500);
        }
        
        return true;
    }
    
    public static function handleException(\Throwable $e): void
    {
        Logger::error("Uncaught Exception: " . $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
        $message = $_ENV['APP_ENV'] === 'production' ? 'Internal server error' : $e->getMessage();
        
        self::sendJsonError($message, $code);
    }
    
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            Logger::critical("Fatal Error: {$error['message']}", $error);
            self::sendJsonError('Critical error occurred', 500);
        }
    }
    
    private static function sendJsonError(string $message, int $code): void
    {
        if (!headers_sent()) {
            http_response_code($code);
        }
        
        echo json_encode([
            'success' => false,
            'error' => $message,
            'timestamp' => date('c')
        ]);
        
        exit;
    }
}
