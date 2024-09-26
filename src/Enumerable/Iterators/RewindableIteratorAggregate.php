<?php

declare(strict_types=1);

namespace Pst\Core\Enumerable\Iterators;

use Pst\Core\CoreObject;

use IteratorAggregate;

class RewindableIteratorAggregate extends CoreObject implements IRewindableIterator, IteratorAggregate {
    use RewindableIteratorTrait;
}