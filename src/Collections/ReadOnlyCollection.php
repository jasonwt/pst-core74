<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\CoreObject;
use Pst\Core\Types\ITypeHint;

use IteratorAggregate;

use BadMethodCallException;

class ReadonlyCollection extends CoreObject implements IteratorAggregate, IReadonlyCollection {
    use ReadonlyCollectionTrait;
    
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
    public static function create(iterable $iterable, ?ITypeHint $T = null, ?ITypeHint $TKey = null): IReadonlyCollection {
        if ($iterable instanceof IReadonlyCollection) {
            return $iterable;
        }

        return new ReadonlyCollection($iterable, $T, $TKey);
    }

    /**
     * Creates a new instance of IReadonlyCollection from an empty collection
     * 
     * @param null|ITypeHint $T 
     * 
     * @return IReadonlyCollection 
     */
    public static function empty(?ITypeHint $T = null, ?ITypeHint $TKey = null): IReadonlyCollection {
        return new ReadonlyCollection([], $T, $TKey);
    }
}