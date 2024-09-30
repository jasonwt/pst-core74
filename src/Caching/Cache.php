<?php

declare(strict_types=1);

namespace Pst\Core\Caching;

use Closure;
use Pst\Core\CoreObject;
use Pst\Core\Exceptions\CacheMissException;

abstract class Cache extends CoreObject implements ICache {
    private IEvictionStrategy $evictionStrategy;

    protected function __construct(IEvictionStrategy $evictionStrategy) {
        $this->evictionStrategy = $evictionStrategy;
    }

    protected abstract function implGetCache(string $key);
    protected abstract function implSetCache(string $key, $value);
    protected abstract function implRemoveCache(string $key);
    
    public function tryGet(string $key, &$value): bool {
        if (!$this->evictionStrategy->has($key)) {
            return false;
        }

        $this->evictionStrategy->touch($key);

        $value = $this->implGetCache($key);
    }

    public function get(string $key, ?Closure $onMissingValue = null) {
        if (!$this->tryGet($key, $value)) {
            if ($onMissingValue === null) {
                throw new CacheMissException($key);
            }

            $value = $onMissingValue();
            $this->set($key, $value);
        }

        return $value;
    }

    public function set(string $key, $value): void {
        $this->evictionStrategy->touch($key);
        $this->implSetCache($key, $value);
    }

    public function delete(string $key): bool {
        return $this->evictionStrategy->evict($key);
    }

    public function clear(): void {
        $this->evictionStrategy->clear();
    }

    public function has(string $key): bool {
        return $this->evictionStrategy->has($key);
    }



}