<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\Types\Type;
use Pst\Core\Types\ITypeHint;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Enumerable\Enumerable;
use Pst\Core\Enumerable\IEnumerable;
use Pst\Core\Enumerable\Linq\Linq;
use Pst\Core\Enumerable\Linq\EnumerableLinqTrait;

use Closure;
use Iterator;
use Generator;
use IteratorAggregate;

use TypeError;
use OutOfBoundsException;
use InvalidArgumentException;

/**
 * Represents read only collection traits.
 * 
 * @package Pst\Core\Collections
 */
trait ReadonlyCollectionTrait {
    use EnumerableLinqTrait {
        count as linqCount;
        contains as linqContains;
        containsKey as linqContainsKey;
    }

    private ?Iterator $source = null;
    private bool $validateSourceKeys = true;
    private bool $validateSourceValues = true;
    
    private array $keyValues = [];

    private ?ITypeHint $T;
    private ?ITypeHint $TKey;

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

        if (!is_array($items)) {
            if ($items instanceof IEnumerable) {
                $T ??= $items->T();
                $TKey ??= $items->TKey();

                if (!$TKey->isAssignableFrom($items->TKey())) {
                    throw new TypeError("Source value type: {$items->TKey()} is not assignable to the specified value type: {$TKey}");
                }

                if (!$T->isAssignableFrom($items->T())) {
                    throw new TypeError("Source value type: {$items->T()} is not assignable to the specified value type: {$T}");
                }

                $this->validateSourceKeys = false;
                $this->validateSourceValues = false;
            } else {
                $this->validateSourceKeys = ($TKey !== null && $TKey->fullName() !== "mixed" && $TKey->fullName() !== "undefined");
                $this->validateSourceValues = ($T !== null && $T->fullName() !== "mixed" && $T->fullName() !== "undefined");
            }

            if (Enumerable::isIterableRewindable($items)) {
                // Check if the iterms is rewindable is important because if it is rewindable
                // it most likely is holding the values somewhere in memory.  we might as well just grab
                // all the values so it might free that memory if it is not being used elsewhere
                // it should also improve performance not loading the values lazily

                $items = Linq::toArray($items);
            } else {
                while ($items instanceof IteratorAggregate) {
                    $items = $items->getIterator();
                }

                $this->source = $items;
            }
        }

        $this->T = $T;
        $this->TKey = $TKey;

        if (is_array($items)) {
            foreach ($items as $key => $value) {
                if ($this->T !== null && $this->validateSourceKeys && !($keyType = Type::typeOf($key))->isAssignableTo($TKey)) {
                    throw new TypeError("Key type: {$keyType} is not assignable to key type {$TKey}");
                }

                if ($this->T !== null && $this->validateSourceValues && !($valueType = Type::typeOf($value))->isAssignableTo($T)) {
                    throw new TypeError("Value type: {$valueType} is not assignable to type {$T}");
                }

                $this->keyValues[$key] = $value;
            }
        }
    }

    /**
     * Populate the next element from the source iterator if it exists
     * 
     * @return null|int|string
     * 
     * @throws TypeError 
     * @throws InvalidArgumentException 
     */
    private function populateNext() {
        if ($this->source !== null) {
            if (!$this->source->valid()) {
                $this->source = null;
                return null;
            }

            $sourceKey = $this->source->key();
            if ($this->validateSourceKeys && !($sourceKeyType = Type::typeOf($sourceKey))->isAssignableTo($this->TKey)) {
                throw new TypeError("{$sourceKeyType} is not assignable to key type {$this->TKey}");
            }

            $sourceValue = $this->source->current();
            if ($this->validateSourceValues && !($sourceType = Type::typeOf($sourceValue))->isAssignableTo($this->T)) {
                throw new TypeError("{$sourceType} is not assignable to type {$this->T}");
            }

            $this->keyValues[$sourceKey] = $sourceValue;
            $this->source->next();

            return $sourceKey;
        }

        return null;
    }

    /**
     * Populate the remaining elements from the source iterator if it exists
     * 
     * @return void 
     * 
     * @throws TypeError 
     * @throws InvalidArgumentException 
     */
    private function populateRemaining(): void {
        if ($this->source === null) {
            return;
        }

        while ($this->populateNext() !== null) {
            continue;
        }
    }

    /**
     * Gets the type hint of the collection values
     * 
     * @return ITypeHint 
     */
    public function T(): ITypeHint {
        return $this->T ?? TypeHintFactory::undefined();
    }

    /**
     * Gets the type hint of the collection keys
     * 
     * @return ITypeHint 
     */
    public function TKey(): ITypeHint {
        return $this->TKey ?? TypeHintFactory::keyTypes();
    }

    /**
     * Gets the number of elements in the collection
     * 
     * @param Closure|null $predicate 
     * 
     * @return int 
     */
    public function count(?Closure $predicate = null): int {
        if (!$this->source !== null) {
            $this->populateRemaining();
        }

        if ($predicate === null) {
            return count($this->keyValues);
        }

        return $this->linqCount($predicate);
    }

    /**
     * Checks if the specified key exists in the collection
     * 
     * @param mixed $key 
     * 
     * @return bool 
     */
    public function offsetExists($key): bool {
        if (array_key_exists($key, $this->keyValues)) {
            return true;
        }

        while (($keyName = $this->populateNext()) !== null) {
            if ($keyName === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the collection is rewindable
     * 
     * @return bool 
     */
    public function isRewindable(): bool {
        return true;
    }

    /**
     * checks if a value exists in the collection
     * 
     * @param mixed $item 
     * 
     * @return bool 
     * 
     * @throws TypeError 
     * @throws InvalidArgumentException 
     */
    public function contains($item): bool {
        foreach ($this->keyValues as $key => $value) {
            if ($value === $item) {
                return true;
            }
        }

        while (($keyName = $this->populateNext()) !== null) {
            if ($this->keyValues[$keyName] === $item) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the specified key exists in the collection
     * 
     * @param mixed $key 
     * 
     * @return bool 
     */
    public function containsKey($key): bool {
        return $this->offsetExists($key);
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
     * Gets the iterator for the collection
     * 
     * @param callable|null $predicate 
     * 
     * @return int 
     */
    public function getIterator(): Iterator {
        if ($this->source === null) {
            return (function(): Generator {
                foreach ($this->keyValues as $key => $value) {
                    yield $key => $value;
                }
            })();
        } else {
            return (function(): Generator {
                $currentKeyIndex = 0;

                if (($currentKey = (array_key_first($this->keyValues) ?? $this->populateNext())) === null) {
                    return;
                }

                while ($currentKeyIndex < count($this->keyValues)) {
                    $currentKeyIndex++;

                    yield $currentKey => $this->keyValues[$currentKey];

                    if ($currentKeyIndex >= count($this->keyValues)) {
                        $currentKey = $this->populateNext();
                    } else {
                        $newKeyValues = array_slice($this->keyValues, $currentKeyIndex, 1, true); // Preserve keys
                        $currentKey = key($newKeyValues);
                    }   
                }
            })();
        }
    }
}