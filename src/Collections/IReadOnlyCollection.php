<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\Enumerable\IRewindableEnumerable;

use Closure;
use ArrayAccess;
use Countable;

interface IReadonlyCollection extends IRewindableEnumerable, ArrayAccess, Countable {
    // readonly methods
    public function contains($item): bool;
    public function containsKey($key): bool;
}