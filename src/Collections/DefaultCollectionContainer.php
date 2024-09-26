<?php

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\CoreObject;
use Pst\Core\Types\Type;
use Pst\Core\Types\ITypeHint;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Enumerable\IEnumerable;
use Pst\Core\Enumerable\Linq\EnumerableLinqTrait;
use Pst\Core\Enumerable\Iterators\RewindableIteratorTrait;

use Closure;
use ArrayIterator;
use IteratorAggregate;

use TypeError;

class DefaultCollectionContainer extends CoreObject implements IteratorAggregate, ICollectionContainer {
    use EnumerableLinqTrait {
        count as private linqCount;
    }

    use RewindableIteratorTrait {
        __construct as private rewindableIteratorTraitConstruct;
        populateNext as private rewindableIteratorTraitPopulateNext;
    }

    private static bool $validateValues = true;

    private ITypeHint $T;
    private ITypeHint $TKey;

    /**
     * Constructor
     * 
     * @param iterable $items 
     * @param null|ITypeHint $T 
     */
    public function __construct(iterable $items = [], ?ITypeHint $T = null, ?ITypeHint $TKey = null) {
        $this->T ??= ($items instanceof IEnumerable ? $items->T() : TypeHintFactory::undefined());
        $this->TKey ??= TypeHintFactory::keyTypes();

        if (!$this->TKey->isAssignableTo(TypeHintFactory::keyTypes())) {
            throw new TypeError("{$this->TKey} is not assignable to key types");
        }
        
        if (is_array($items)) {
            $this->rewindableIteratorTraitConstruct($items, false);

        } else if ($items instanceof ArrayIterator) {
            $this->rewindableIteratorTraitConstruct(iterator_to_array($items), false);

        } else {
            $this->rewindableIteratorTraitConstruct($items, true);
        }
    }

    /**
     * Populate the next element (override to validate values if enabled)
     * 
     * @return mixed 
     */
    private function populateNext() {
        if ($this->iterator === null) {
            return null;
        }

        if (($populateNextKey = $this->rewindableIteratorTraitPopulateNext()) === null) {
            return null;
        }

        if (static::$validateValues) {
            $value = $this->keyValues[$populateNextKey];

            $valueType = Type::typeOf($value);

            if (!$this->T->isAssignableFrom($valueType)) {
                throw new TypeError("{$this->T} is not assignable from {$valueType}");
            }
        }

        return $populateNextKey;
    }

    /**
     * Gets the value type hint
     * 
     * @return ITypeHint 
     */
    public function T(): ITypeHint {
        return $this->T;
    }

    /**
     * Gets the key type hint
     * 
     * @return ITypeHint 
     */
    public function TKey(): ITypeHint {
        return $this->TKey;
    }

    /**
     * Gets the number of items in the collection with an optional predicate
     * 
     * @return ITypeHint 
     */
    public function count(?Closure $predicate = null): int {
        if ($predicate === null) {
            return count($this->keyValues);
        }

        return $this->linqCount($predicate);
    }

    /**
     * Clears the collection
     * 
     * @return void
     */
    public function clear(): void {
        $this->currentKey = null;
        $this->currentKeyIndex = 0;
        $this->iterator = null;
        $this->keyValues = [];
    }
    /**
     * Gets the value of the specified key index
     * 
     * @param mixed $key 
     * 
     * @return mixed 
     */
    public function offsetExists($offset): bool {
        if (array_key_exists($offset, $this->keyValues)) {
            return true;
        } else if ($this->iterator === null) {
            return false;
        }

        while (($key = $this->populateNext()) !== null) {
            if ($key === $offset) {
                return true;
            }
        } 

        return false;
    }
    
    /**
     * Gets the value of the specified key index
     * 
     * @param mixed $key 
     * 
     * @return mixed 
     */
    public function offsetGet($offset) {
        if (array_key_exists($offset, $this->keyValues)) {
            return $this->keyValues[$offset];

        } else if ($this->iterator !== null) {
            while (($key = $this->populateNext()) !== null) {
                if ($key === $offset) {
                    return $this->keyValues[$key];
                }
            }
        }

        return null;
    }

    /**
     * Sets the value of the specified key index
     * 
     * @param mixed $key 
     * @param mixed $value 
     */
    public function offsetSet($offset, $value): void {
        if (static::$validateValues) {
            $valueType = Type::typeOf($value);

            if (!$this->T->isAssignableFrom($valueType)) {
                throw new TypeError("{$this->T} is not assignable from {$valueType}");
            }
        }

        if ($offset === null) {
            $this->populateRemaining();

            $this->keyValues[] = $value;

        } else if ($this->iterator === null) {
            $this->keyValues[$offset] = $value;

        } else if ($this->offsetExists($offset)) {
            $this->keyValues[$offset] = $value;

        } else {
            while (($key = $this->populateNext()) !== null) {
                if ($key === $offset) {
                    $this->keyValues[$key] = $value;

                    return;
                }
            }

            $this->keyValues[$offset] = $value;
        }
    }

    /**
     * Unsets the value of the specified key index
     * 
     * @param mixed $key 
     */
    public function offsetUnset($offset): void {
        if ($this->offsetExists($offset)) {
            unset($this->keyValues[$offset]);
        } 
    }
}