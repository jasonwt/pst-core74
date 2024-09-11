<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use ArrayAccess;
use Closure;
use Countable;

interface IReadOnlyCollection extends ArrayAccess, IEnumerable, Countable {
    public function count(?Closure $predicate = null): int;
    public function contains($item): bool;
    public function containsKey($key): bool;
    public function indexOf($item);
}