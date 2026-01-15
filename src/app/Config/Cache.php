<?php

namespace App\Config;

use Redis;

class Cache
{
    private static ?Redis $redis = null;
    private static bool $enabled = true;
    
    private static function connect(): ?Redis
    {
        if (self::$redis !== null) {
            return self::$redis;
        }
        
        try {
            self::$redis = new Redis();
            self::$redis->connect(
                $_ENV['REDIS_HOST'] ?? 'redis',
                (int)($_ENV['REDIS_PORT'] ?? 6379),
                2
            );
            
            self::$redis->ping();
            
            return self::$redis;
        } catch (\Exception $e) {
            Logger::error("Redis connection failed: " . $e->getMessage());
            self::$enabled = false;
            return null;
        }
    }
    
    public static function get(string $key)
    {
        if (!self::$enabled) {
            return null;
        }
        
        $redis = self::connect();
        if (!$redis) {
            return null;
        }
        
        try {
            $data = $redis->get($key);
            return $data !== false ? json_decode($data, true) : null;
        } catch (\Exception $e) {
            Logger::error("Cache get failed: " . $e->getMessage(), ['key' => $key]);
            return null;
        }
    }
    
    public static function set(string $key, $value, int $ttl = 3600): bool
    {
        if (!self::$enabled) {
            return false;
        }
        
        $redis = self::connect();
        if (!$redis) {
            return false;
        }
        
        try {
            return $redis->setex($key, $ttl, json_encode($value));
        } catch (\Exception $e) {
            Logger::error("Cache set failed: " . $e->getMessage(), ['key' => $key]);
            return false;
        }
    }
    
    public static function delete(string $key): bool
    {
        if (!self::$enabled) {
            return false;
        }
        
        $redis = self::connect();
        if (!$redis) {
            return false;
        }
        
        try {
            return $redis->del($key) > 0;
        } catch (\Exception $e) {
            Logger::error("Cache delete failed: " . $e->getMessage(), ['key' => $key]);
            return false;
        }
    }
    
    public static function flush(): bool
    {
        if (!self::$enabled) {
            return false;
        }
        
        $redis = self::connect();
        if (!$redis) {
            return false;
        }
        
        try {
            return $redis->flushDB();
        } catch (\Exception $e) {
            Logger::error("Cache flush failed: " . $e->getMessage());
            return false;
        }
    }
    
    public static function remember(string $key, int $ttl, callable $callback)
    {
        $cached = self::get($key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $value = $callback();
        self::set($key, $value, $ttl);
        
        return $value;
    }
}
