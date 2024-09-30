<?php

declare(strict_types=1);

namespace Pst\Core;

use Closure;

trait CachingTrait {
    private static array $cache = [];

    private static function cacheExists(string $key): bool {
        return array_key_exists($key, self::$cache);
    }

    private static function writeToCache(string $key, mixed $value): void {
        self::$cache[$key] = $value;
    }

    private static function removeFromCache(string $key): void {
        unset(self::$cache[$key]);
    }

    private static function readFromCache(string $key): mixed {
        if (!self::cacheExists($key)) {
            throw new \Exception("Cache key $key does not exist.");
        }
        return self::$cache[$key];
    }
}