<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\Types\Type;
use Pst\Core\Types\ITypeHint;
use Pst\Core\Enumerable\EnumerableTrait;
use Pst\Core\Enumerable\Linq\Linq;

use Traversable;

use TypeError;
use OutOfBoundsException;
use BadMethodCallException;
use InvalidArgumentException;

/**
 * Represents a collection traits.
 * 
 * @package Pst\Core\Collections
 * 
 * @version 1.0.0
 * 
 * @since 1.0.0
 */
trait CollectionTrait {
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
    public function __construct(iterable $items = [], ?ITypeHint $T = null, ?ITypeHint $TKey = null) {
        $this->collectionContainer = new DefaultCollectionContainer($items, $T, $TKey);
    }

    /**
     * Returns if the specified key exists in the collection
     * 
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key): bool {
        return $this->collectionContainer->offsetExists($key);
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
        if (!$this->collectionContainer->offsetExists($key)) {
            throw new OutOfBoundsException("Key: '{$key}' does not exist.");
        }

        return $this->collectionContainer->offsetGet($key);
    }

    /**
     * Sets the value of the specified key index (disabled for ReadonlyCollections, will throw BadMethodCallException)
     * 
     * @param mixed $key 
     * @param mixed $value 
     * 
     * @return void 
     */
    public function offsetSet($key, $value): void {
        $this->collectionContainer->offsetSet($key, $value);
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
        if (!$this->collectionContainer->offsetExists($key)) {
            throw new OutOfBoundsException("Key: '{$key}' does not exist.");
        }

        $this->collectionContainer->offsetUnset($key);
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
        return $this->collectionContainer->iterationCount(fn($x) => $x === $item);
    }

    /**
     * 
     * Tries to add an item to the collection.
     * 
     * @param mixed $item
     * @param mixed $key
     * 
     * @throws \Exception
     * @return bool
     */
    public function tryAdd($item, $key = null): bool {
        if ($this->collectionContainer->offsetExists($key)) {
            return false;
        }

        if ($key === null) {
            $this->collectionContainer->offsetSet(null, $item);
        } else if (!is_string($key) || !is_int($key)) {
            throw new InvalidArgumentException("Key must be a string or an integer.");
        } else {
            $this->collectionContainer->offsetSet($key, $item);
        }

        return true;
    }

    /**
     * Adds an item to the collection.
     * 
     * @param mixed $item
     * @param mixed $key
     * 
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    public function add($item, $key = null): void {
        if (!$this->tryAdd($item, $key)) {
            throw new InvalidArgumentException("Key: {$key} already exists.");
        }
    }

    /**
     * Clears the collection.
     * 
     * @return void
     */
    public function clear(): void {
        $this->collectionContainer->clear();
    }

    /**
     * Removes an item from the collection.
     * 
     * @param mixed $key
     * 
     * @return bool
     * 
     * @throws InvalidArgumentException
     */
    public function remove($key): bool {
        if (!$this->collectionContainer->offsetExists($key)) {
            return false;
        }

        if (!is_string($key) || !is_int($key)) {
            throw new InvalidArgumentException("Key must be a string or an integer.");
        }

        $this->collectionContainer->offsetUnset($key);

        return true;
    }
}