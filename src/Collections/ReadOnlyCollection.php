<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\CoreObject;
use Pst\Core\Types\ITypeHint;

use Pst\Core\Collections\Traits\CollectionTrait;

use BadMethodCallException;

class ReadOnlyCollection extends CoreObject implements IReadOnlyCollection {
    use CollectionTrait {
        add as private;
        clear as private;
        remove as private;
        offsetSet as private collectionTraitOffsetSet;
        offsetUnset as private collectionTraitOffsetUnset;
    }

    public function offsetSet($key, $value): void {
        throw new BadMethodCallException('Cannot modify a read-only collection.');
    }

    public function offsetUnset($key): void {
        throw new BadMethodCallException('Cannot modify a read-only collection.');
    }

    public static function new($iterable, ?ITypeHint $T = null): IReadOnlyCollection {
        return new ReadOnlyCollection($iterable, $T);
    }
}