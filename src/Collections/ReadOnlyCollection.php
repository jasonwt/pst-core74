<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\CoreObject;
use Pst\Core\Types\ITypeHint;

use Pst\Core\Enumerable\Enumerator;
use Pst\Core\Enumerable\ImmutableEnumerableLinqTrait;

use Iterator;
use BadMethodCallException;

class ReadonlyCollection extends CoreObject implements Iterator, IReadonlyCollection {
    use ImmutableEnumerableLinqTrait;
    
    use CollectionTrait {
        add as private;
        clear as private;
        remove as private;
        offsetSet as private collectionTraitOffsetSet;
        offsetUnset as private collectionTraitOffsetUnset;
    }

    /**
     * Adds an item to the collection (disabled for ReadonlyCollections, will throw BadMethodCallException)
     * 
     * @param mixed $item 
     * 
     * @return void 
     * 
     * @throws BadMethodCallException 
     */
    public function offsetSet($key, $value): void {
        throw new BadMethodCallException('Cannot modify a read-only collection.');
    }

    /**
     * Removes an item from the collection (disabled for ReadonlyCollections, will throw BadMethodCallException)
     * 
     * @param mixed $key 
     * 
     * @return void 
     * 
     * @throws BadMethodCallException 
     */
    public function offsetUnset($key): void {
        throw new BadMethodCallException('Cannot modify a read-only collection.');
    }

    /**
     * Creates a new instance of IReadonlyCollection
     * 
     * @param iterable $iterable 
     * @param null|ITypeHint $T 
     * 
     * @return IReadonlyCollection 
     */
    public static function new(iterable $iterable, ?ITypeHint $T = null): IReadonlyCollection {
        if ($iterable instanceof IReadonlyCollection) {
            return $iterable;
        }

        return new ReadonlyCollection(Enumerator::new($iterable), $T);
    }

    /**
     * Creates a new instance of IReadonlyCollection from an empty collection
     * 
     * @param null|ITypeHint $T 
     * 
     * @return IReadonlyCollection 
     */
    public static function empty(?ITypeHint $T = null): IReadonlyCollection {
        return new ReadonlyCollection([], $T);
    }
}