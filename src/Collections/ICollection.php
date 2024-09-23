<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\Interfaces\ICloneable;
use Pst\Core\Enumerable\IEnumerable;

use Closure;
use ArrayAccess;

interface ICollection extends IEnumerable, ICloneable, ArrayAccess  {
    // readonly methods
    public function count(?Closure $predicate = null): int;
    public function contains($item): bool;
    public function containsKey($key): bool;
    public function indexOf($item);

    // mutable methods
    public function tryAdd($item, $key = null): bool;
    public function add($item, $key = null);
    public function clear();
    public function remove($key): bool;
}