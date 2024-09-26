<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\Enumerable\IRewindableEnumerable;

use Closure;
use ArrayAccess;

interface IReadonlyCollection extends IRewindableEnumerable, ArrayAccess {
    // readonly methods
    public function count(?Closure $predicate = null): int;
    public function contains($item): bool;
    public function containsKey($key): bool;
    public function indexOf($item): int;
}