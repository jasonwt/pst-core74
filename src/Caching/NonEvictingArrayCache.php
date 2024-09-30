<?php

declare(strict_types=1);

namespace Pst\Core\Caching;

use Pst\Core\CoreObject;

use Pst\Core\Exceptions\CacheMissException;

use Closure;

class NonEvictingArrayCache extends CoreObject implements ICache {
    private array $cache = [];

    public function tryGet(string $key, &$value): bool {
        return (($value = $this->cache[$key]) !== null || array_key_exists($key, $this->cache));
    }

    public function get(string $key, ?Closure $onMissingValue = null) {
        if (!$this->tryGet($key, $value, $onMissingValue)) {
            if ($onMissingValue === null) {
                throw new CacheMissException($key);
            }
                
            $this->cache[$key] = $value = $onMissingValue();
            
        }

        return $value;
    }

    public function set(string $key, $value): void {
        $this->cache[$key] = $value;
    }

    public function delete(string $key): bool {
        if (isset($this->cache[$key]) || array_key_exists($key, $this->cache)) {
            unset($this->cache[$key]);
            return true;
        }
        
        return false;
    }

    public function clear(): void {
        $this->cache = [];
    }

    public function has(string $key): bool {
        return isset($this->cache[$key]) || array_key_exists($key, $this->cache);
    }
}