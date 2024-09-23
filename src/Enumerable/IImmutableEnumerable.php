<?php

declare(strict_types=1);

namespace Pst\Core\Enumerable;

use Pst\Core\Interfaces\IToArray;
use Pst\Core\Types\ITypeHint;

use Countable;

interface IImmutableEnumerable extends IImmutableEnumerableLinq, IEnumerable, Countable {
    public function T(): ITypeHint;
}