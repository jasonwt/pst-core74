<?php

declare(strict_types=1);

namespace Pst\Core\Caching;

use Closure;
use Pst\Core\Interfaces\ICoreObject;

interface ICache extends ICoreObject {
    public function tryGet(string $key, &$value): bool;
    public function get(string $key, ?Closure $onMissingSet = null);
    public function set(string $key, $value): void;
    public function delete(string $key): bool;
    public function clear(): void;
    public function has(string $key): bool;
}