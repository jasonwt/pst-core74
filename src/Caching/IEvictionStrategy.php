<?php

declare(strict_types=1);

namespace Pst\Core\Caching;

use Pst\Core\Interfaces\ICoreObject;

interface IEvictionStrategy extends ICoreObject {
    public function remainingCapacity(): ?int;
    public function isExpired(string $key): bool;
    public function has(string $key): bool;
    public function touch(string $key): void;
    public function evict(string $key): bool;
    public function clear(): void;
    public function evictNext(): ?bool;
}