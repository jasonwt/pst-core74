<?php

declare(strict_types=1);

namespace Pst\Core\Enumerable\Iterators;

use Pst\Core\CoreObject;

use IteratorAggregate;

final class RewindableIteratorAggregate extends CoreObject implements IRewindableIterator, IteratorAggregate {
    use RewindableIteratorTrait;

    public static function create(iterable $iterator, bool $rewindable = true): IRewindableIterator {
        return new static($iterator, $rewindable);
    }
}