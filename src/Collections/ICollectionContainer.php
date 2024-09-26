<?php

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\Enumerable\IEnumerable;

use Countable;
use ArrayAccess;

interface ICollectionContainer extends IEnumerable, ArrayAccess, Countable {
    public function clear(): void;
}