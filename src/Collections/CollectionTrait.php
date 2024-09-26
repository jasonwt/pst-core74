<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\Types\Type;

use TypeError;
use OutOfBoundsException;
use BadMethodCallException;
use InvalidArgumentException;

/**
 * Represents read only collection traits.
 * 
 * @package Pst\Core\Collections
 */
trait CollectionTrait {
    use ReadonlyCollectionTrait;

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
        if ($this->TKey !== null && !($keyType = Type::typeOf($key))->isAssignableTo($this->TKey)) {
            throw new TypeError("Key type: {$keyType} is not assignable to key type {$this->TKey}");
        }

        if ($this->T !== null && !($valueType = Type::typeOf($value))->isAssignableTo($this->T)) {
            throw new TypeError("Value type: {$valueType} is not assignable to type {$this->T}");
        }

        if ($this->offsetExists($key)) {
            // new record
        }
            
        $this->keyValues[$key] = $value;
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
        if (!$this->offsetExists($key)) {
            throw new OutOfBoundsException("Key: '{$key}' does not exist.");
        }

        unset($this->keyValues[$key]);
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
        if ($key === null) {
            $this->keyValues[] = $item;
        } else {
            if ($this->offsetExists($key)) {
                return false;
            }

            $this->keyValues[$key] = $item;
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
        $this->source = null;
        $this->keyValues = [];
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
        if (!$this->offsetExists($key)) {
            return false;
        }

        unset($this->keyValues[$key]);

        return true;
    }
}