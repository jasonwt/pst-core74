<?php

declare(strict_types=1);

namespace Pst\Core\Caching;

use Closure;
use Pst\Core\CoreObject;
use Pst\Core\Exceptions\CacheMissException;

class ArrayCache extends CoreObject implements ICache {
    private IEvictionStrategy $evictionStrategy;

    private array $cacheValues = [];

    public function __construct(?IEvictionStrategy $evictionStrategy = null, array $cacheValues = []) {
        $this->evictionStrategy = $evictionStrategy ?? new LRUEvictionStrategy(0);
    }

    public function tryGet(string $key, &$value): bool {
        if (!$this->evictionStrategy->has($key)) {
            return false;
        }

        $this->evictionStrategy->touch($key);

        $value = $this->cacheValues[$key];

        return true;
    }

    public function get(string $key, ?Closure $onMissingValue = null) {
        if (!$this->tryGet($key, $value)) {
            if ($onMissingValue === null) {
                throw new CacheMissException($key);
            }

            $this->cacheValues[$key] = $value = $onMissingValue();
            $this->evictionStrategy->touch($key);
        }

        return $value;
    }

    public function set(string $key, $value): void {
        while (($remainingCapacity = $this->evictionStrategy->remainingCapacity()) !== null && $remainingCapacity <= 0) {
            $this->evictionStrategy->evictNext();
        }
        
        $this->cacheValues[$key] = $value;
        $this->evictionStrategy->touch($key);
    }

    public function delete(string $key): bool {
        if (!isset($this->cacheValues[$key])) {
            return false;
        } else {
            unset($this->cacheValues[$key]);
        }

        return $this->evictionStrategy->evict($key);
    }

    public function clear(): void {
        $this->cacheValues = [];
        $this->evictionStrategy->clear();
    }

    public function has(string $key): bool {
        return $this->evictionStrategy->has($key);
    }
}