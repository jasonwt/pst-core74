<?php

declare(strict_types=1);

namespace Pst\Core\Enumerable\Iterators;

use Pst\Core\CoreObject;

use Iterator;

final class RewindableIterator extends CoreObject implements IRewindableIterator, Iterator {
    use RewindableIteratorTrait;

    public static function create(iterable $iterator, bool $rewindable = true): IRewindableIterator {
        return new static($iterator, $rewindable);
    }
}