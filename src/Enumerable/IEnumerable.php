<?php

declare(strict_types=1);

namespace Pst\Core\Enumerable;

use Pst\Core\Interfaces\IToArray;
use Pst\Core\Types\ITypeHint;
use Pst\Core\Collections\IToCollection;
use Pst\Core\Collections\IToReadonlyCollection;

use Traversable;

interface IEnumerable extends Traversable, IToArray, IToReadonlyCollection, IToCollection {
    public function T(): ITypeHint;

    // public function current();
    // public function key();
    // public function next();
    // public function valid(): bool;
}