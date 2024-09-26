<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\Types\ITypeHint;
use Pst\Core\Enumerable\EnumerableTrait;
use Pst\Core\Enumerable\Linq\Linq;

use Traversable;
use TypeError;
use OutOfBoundsException;
use BadMethodCallException;
use InvalidArgumentException;

/**
 * Represents read only collection traits.
 * 
 * @package Pst\Core\Collections
 * 
 * @version 1.0.0
 * 
 * @since 1.0.0
 */
trait ReadonlyCollectionTrait {
    use EnumerableTrait {
        __construct as private enumerableTraitConstruct;
    }

    private ICollectionContainer $collectionContainer;

    /**
     * Creates a new instance of Enumerable
     * 
     * @param iterable $items 
     * @param ITypeHint|null $T 
     * 
     * @throws TypeError 
     * @throws InvalidArgumentException 
     */
    public function __construct(iterable $items = [], ?ITypeHint $T = null) {
        $this->collectionContainer = new DefaultCollectionContainer($items, $T);
    }

    /**
     * Gets a from the specified key index
     * 
     * @param mixed $key 
     * 
     * @return mixed 
     * 
     * @throws TypeError 
     * @throws OutOfBoundsException 
     * @throws InvalidArgumentException 
     */
    public function offsetGet($key) {
        if (!$this->offsetExists($key)) {
            throw new OutOfBoundsException("Key: '{$key}' does not exist.");
        }

        return $this->keyValues[$key];
    }

    /**
     * Sets the value of the specified key index (disabled for ReadonlyCollections, will throw BadMethodCallException)
     * 
     * @param mixed $key 
     * @param mixed $value 
     * 
     * @return void 
     * 
     * @throws BadMethodCallException 
     */
    public function offsetSet($key, $value): void {
        throw new BadMethodCallException('Cannot modify a read-only collection.');
    }

    /**
     * Removes the value of the specified key index (disabled for ReadonlyCollections, will throw BadMethodCallException)
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
     * Gets the iterator for the collection
     * 
     * @param callable|null $predicate 
     * 
     * @return int 
     */
    public function getIterator(): Traversable {
        return $this->collectionContainer;
    }

    /**
     * Gets the key index of the specified item
     * 
     * @param mixed $item 
     * 
     * @return int 
     */
    public function indexOf($item): int {
        return Linq::iterationCount($this, fn($x) => $x === $item);
    }

    /**
     * Gets the key of the specified item
     * 
     * @param mixed $item 
     * 
     * @return mixed 
     */
    public function keyOf($item) {
        foreach ($this->keyValues as $key => $value) {
            if ($value === $item) {
                return $key;
            }
        }

        return null;
    }
}