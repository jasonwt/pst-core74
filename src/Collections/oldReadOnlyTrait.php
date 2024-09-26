<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\Types\Type;
use Pst\Core\Types\ITypeHint;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Enumerable\Enumerator;
use Pst\Core\Enumerable\IEnumerable;
use Pst\Core\Enumerable\IImmutableEnumerable;

use Closure;
use Exception;
use Traversable;
use ArrayIterator;

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
trait oldReadonlyCollectionTrait {
    private $collectionTraitCurrentKey = null;
    private int $collectionTraitCurrentKeyIndex = 0;

    private ?IEnumerable $collectionTraitItemsEnumerator = null;
    private array $collectionTraitItems = [];
    private string $collectionTraitT;
    
    public function __construct(iterable $iterable, ?ITypeHint $T = null) {
        if (is_array($iterable)) {
            $this->collectionTraitItems = $iterable;
        } else {
            $this->collectionTraitItemsEnumerator = Enumerator::create($iterable);
            $T ??= $this->collectionTraitItemsEnumerator->T();
        }
 
        $this->collectionTraitT = (string) ($T ?? TypeHintFactory::undefined());

        if ($this->collectionTraitT === "void") {
            throw new InvalidArgumentException("Type hint cannot be void");
        }
    }

    public function T(): ITypeHint {
        return TypeHintFactory::tryParse($this->collectionTraitT);
    }

    public function current() {
        return $this->collectionTraitItems[$this->collectionTraitCurrentKey];
    }

    public function key() {
        return $this->collectionTraitCurrentKey;
    }

    public function next(): void {
        if ($this->collectionTraitCurrentIndex < count($this->collectionTraitItems)) {
            $this->collectionTraitCurrentKeyIndex ++;    
        }
    } 

    public function rewind(): void {
        $this->collectionTraitCurrentKey = null;
        $this->collectionTraitCurrentKeyIndex = 0;
    }

    public function valid(): bool {
        if ($this->collectionTraitCurrentKeyIndex >= count($this->collectionTraitItems)) {
            $this->populateNext();
        }

        if ($this->collectionTraitCurrentKeyIndex >= count($this->collectionTraitItems)) {
            return false;
        }

        $newKeyValues = array_slice($this->collectionTraitItems, $this->collectionTraitCurrentKeyIndex, 1, true); // Preserve keys

        $this->collectionTraitCurrentKey = key($newKeyValues);

        return true;
    }

    private function populateNext(): ?array {
        if ($this->collectionTraitItemsEnumerator === null) {
            return null;
        } else if (!$this->collectionTraitItemsEnumerator->valid()) {
            $this->collectionTraitItemsEnumerator = null;
            return null;
        }

        $key = $this->collectionTraitItemsEnumerator->key();
        $value = $this->collectionTraitItemsEnumerator->current();

        $itemsTypeHint = TypeHintFactory::tryParse($this->collectionTraitT);

        if (!$itemsTypeHint->isAssignableFrom(Type::typeOf($value))) {
            throw new InvalidArgumentException("Item is not assignable to the type hint.");
        }

        $this->collectionTraitItems[$key] = $value;

        $this->collectionTraitItemsEnumerator->next();

        return [$key, $value];
    }

    public function getIterator(): Traversable {
        if ($this->collectionTraitItemsEnumerator === null) {
            
            yield from new ArrayIterator($this->collectionTraitItems);
        } else {
            $this->rewind();

            while ($this->valid()) {
                yield $this->key() => $this->current();
                $this->next();
            }
        }
    }

    public function offsetExists($key): bool {
        if (!is_string($key) && !is_int($key)) {
            throw new Exception("Key must be a string or an integer.");
        }

        if (array_key_exists($key, $this->collectionTraitItems)) {
            return true;
        }

        while (($nextValue = $this->populateNext()) !== null) {
            if ($nextValue[0] === $key) {
                return true;
            }
        }

        return false;
    }

    public function offsetGet($key) {
        if (!$this->offsetExists($key)) {
            throw new Exception("Key: '{(string) $key}' does not exist.");
        }

        return $this->collectionTraitItems[$key];
    }

    public function count(?Closure $predicate = null): int {
        while ($this->populateNext() !== null);

        if ($predicate !== null) {
            return $this->linqCount($predicate);
        }

        return $predicate !== null ? $this->linqCount($predicate) : count($this->collectionTraitItems);
    }

    public function containsKey($key): bool {
        return $this->offsetExists($key);
    }

    public function contains($item): bool {
        if (in_array($item, $this->collectionTraitItems)) {
            return true;
        }

        while (($next = $this->populateNext()) !== null) {
            if ($next[1] === $item) {
                return true;
            }
        }

        return false;
    }

    public function indexOf($item) {
        if (($indexOf = array_search($item, $this->collectionTraitItems)) !== false) {
            return $indexOf;
        }

        while (($next = $this->populateNext()) !== null) {
            if ($next[1] === $item) {
                return $next[0];
            }
        }
        
        return -1;
    }

    public function keys(?Closure $predicate = null): IImmutableEnumerable {
        while ($this->populateNext() !== null);

        if ($predicate === null) {
            return Enumerator::create(array_keys($this->collectionTraitItems), TypeHintFactory::keyTypes());
        }

        return $this->linqKeys($predicate);
        
    }

    public function values(?Closure $predicate = null): IImmutableEnumerable {
        while ($this->populateNext() !== null);

        if ($predicate === null) {
            return Enumerator::create(array_values($this->collectionTraitItems), TypeHintFactory::tryParse($this->collectionTraitT));
        }

        return $this->linqValues($predicate);
    }

    public function clone(): self {
        return static::new($this->toArray(), $this->T());
    }
}