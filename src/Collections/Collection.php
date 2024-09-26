<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\CoreObject;
use Pst\Core\Collections\ICollection;

use IteratorAggregate;
use Pst\Core\Types\ITypeHint;

class Collection extends CoreObject implements IteratorAggregate, ICollection {
    use CollectionTrait;

    public static function create(iterable $collection, ?ITypeHint $T = null, ?ITypeHint $TKey = null): ICollection {
        return new static($collection, $T, $TKey);
    }
}