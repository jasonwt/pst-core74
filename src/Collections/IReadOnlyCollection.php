<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\Interfaces\ICloneable;
use Pst\Core\Enumerable\IImmutableEnumerable;

use Closure;
use ArrayAccess;

interface IReadonlyCollection extends IImmutableEnumerable, ICloneable, ArrayAccess {
    // readonly methods
    public function count(?Closure $predicate = null): int;
    public function contains($item): bool;
    public function containsKey($key): bool;
    public function indexOf($item);
}