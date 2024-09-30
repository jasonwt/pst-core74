<?php

declare(strict_types=1);

namespace Pst\Core\Caching;

use Closure;
use InvalidArgumentException;

final class Caching {
    private static array $caches = [];

    private function __construct() {}

    public static function registerCache(string $name, ICache $cache): void {
        if (empty($name = trim($name))) {
            throw new InvalidArgumentException("Cache name cannot be empty");
        }

        if (isset(self::$caches[$name])) {
            throw new InvalidArgumentException("Cache {$name} already exists");
        }

        self::$caches[$name] = $cache;
    }

    public static function unregisterCache(string $name): void {
        unset(self::$caches[$name]);
    }

    public static function tryGetCache(string $name, ?ICache &$cache): bool {
        if (($cache = self::$caches[$name]) === null) {
            return false;
        }

        return true;
    }

    public static function getCache(?string $name = null): ICache {
        if ($name === null) {
            return self::$caches['default'] ??= new NonEvictingArrayCache();
        }
        
        if (($cache = self::$caches[$name]) === null) {
            throw new InvalidArgumentException("Cache {$name} does not exist");
        }

        return $cache;
    }

    public static function tryGet(string $key, &$value, ?string $cacheName = null): bool {
        if ($cacheName === null) {
            return (self::$caches['default'] ??= new NonEvictingArrayCache())->tryGet($key, $value);
        } else if (!isset(self::$caches[$cacheName])) {
            return false;
        }

        return self::$caches[$cacheName]->tryGet($key, $value);
    }

    public static function get(string $key, ?string $cacheName = null) {
        if ($cacheName === null) {
            return (self::$caches['default'] ??= new NonEvictingArrayCache())->get($key, $cacheName);
        } else if (!isset(self::$caches[$cacheName])) {
            return false;
        }

        return self::$caches[$cacheName]->get($key, $cacheName);
    }

    public static function getWithSet(string $key, Closure $onMissingSet, ?string $cacheName = null) {
        if ($cacheName === null) {
            return (self::$caches['default'] ??= new NonEvictingArrayCache())->get($key, $onMissingSet);

        } else if (!isset(self::$caches[$cacheName])) {
            throw new InvalidArgumentException("Cache {$cacheName} does not exist");
        }

        return self::$caches[$cacheName]->get($key, $onMissingSet);
    }

    public static function set(string $key, $value, ?string $cacheName = null): void {
        if ($cacheName === null) {
            (self::$caches['default'] ??= new NonEvictingArrayCache())->set($key, $value);
            return;

        } else if (!isset(self::$caches[$cacheName])) {
            return;
        }

        self::$caches[$cacheName]->set($key, $value);
    }

    public static function delete(string $key, ?string $cacheName = null): bool {
        if ($cacheName === null) {
            return (self::$caches['default'] ??= new NonEvictingArrayCache())->delete($key);

        } else if (!isset(self::$caches[$cacheName])) {
            return false;
        }

        return self::$caches[$cacheName]->delete($key);
    }

    public static function clear(string $cacheName): void {
        if (!isset(self::$caches[$cacheName])) {
            return;
        }

        self::$caches[$cacheName]->clear();
    }

    public static function has(string $key, ?string $cacheName = null): bool {
        if ($cacheName === null) {
            return (self::$caches['default'] ??= new NonEvictingArrayCache())->has($key);

        } else if (!isset(self::$caches[$cacheName])) {
            return false;
        }

        return self::$caches[$cacheName]->has($key);
    }
}