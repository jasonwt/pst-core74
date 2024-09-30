<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\Interfaces\IReadonly;
use Pst\Core\Enumerable\IRewindableEnumerable;

use ArrayAccess;
use Countable;

interface IReadonlyCollection extends IRewindableEnumerable, ArrayAccess, Countable, IReadonly {
    // readonly methods
    public function contains($item): bool;
    public function containsKey($key): bool;
}