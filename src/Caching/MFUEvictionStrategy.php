<?php

declare(strict_types=1);

namespace Pst\Core\Caching;

use Pst\Core\CoreObject;

use InvalidArgumentException;

class MRUEvictionStrategy extends CoreObject implements IEvictionStrategy {
    private array $cacheKeys = [];
    private int $maxItems = 0;

    public function __construct(int $maxItems) {
        if ($maxItems < 0) {
            throw new InvalidArgumentException('maxItems must be greater than or equal to 0');
        }

        $this->maxItems = $maxItems;
    }

    public function isExpired(string $key): bool {
        if ($this->maxItems === 0) {
            return false;
        }

        return count($this->cacheKeys) > $this->maxItems && array_key_last($this->cacheKeys) == $key;
    }

    public function has(string $key): bool {
        return isset($this->cacheKeys[$key]);
    }

    public function evict(string $key): bool {
        if (!isset($this->cacheKeys[$key])) {
            return false;
        }

        unset($this->cacheKeys[$key]);
    }

    public function remainingCapacity(): ?int {
        return $this->maxItems === null ? null : 
            $this->maxItems - count($this->cacheKeys);
    }

    public function touch(string $key): void {
        if (isset($this->cacheKeys[$key])) {
            unset($this->cacheKeys[$key]);
        }

        $this->cacheKeys[$key] = time();
    }

    public function clear(): void {
        $this->cacheKeys = [];
    }

    public function evictNext(): bool {
        if (($nextEvictionKey = array_key_first($this->cacheKeys)) === null) {
            return false;
        }
        
        unset($this->cacheKeys[$nextEvictionKey]);
        return true;
    }
}