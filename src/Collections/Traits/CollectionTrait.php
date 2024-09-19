<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections\Traits;

use Pst\Core\Types\Type;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Types\ITypeHint;

use Pst\Core\Collections\Enumerator;
use Pst\Core\Collections\IEnumerable;
use Pst\Core\Collections\ReadOnlyCollection;

use Closure;
use Exception;
use Traversable;
use ArrayIterator;
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
    use LinqTrait {
        count as private linqCount;
        keys as private linqKeys;
        values as private linqValues;
    }

    private string $collectionTraitT;
    private array $collectionTraitItems = [];

    public function __construct($iterable, ?ITypeHint $T = null) {
        $iterableT = null;

        if (is_array($iterable)) {
            $iterable = new ArrayIterator($iterable);
        } else if ($iterable instanceof IEnumerable) {
            $iterableT = $iterable->T();
            $iterable = $iterable->getIterator();
        } else if (!$iterable instanceof Traversable) {
            throw new InvalidArgumentException("iterable argument must implement ICollection, Traversable or be iterable.");
        }

        if ($iterableT !== null) {
            if ($T !== null && !$T->isAssignableFrom($iterableT)) {
                throw new InvalidArgumentException("Type hint mismatch.");
            }

            $T = $iterableT;
        }

        $this->collectionTraitT = (string) ($T ?? TypeHintFactory::undefined());
        $this->collectionTraitItems = [];

        if ($this->collectionTraitT === "void") {
            throw new InvalidArgumentException("Type hint cannot be void");
        }

        foreach ($iterable as $key => $value) {
            $this->add($value, $key);
        }
    }

    public function T(): ITypeHint {
        return TypeHintFactory::tryParse($this->collectionTraitT);
    }

    public function getIterator(): Traversable {
        return new ArrayIterator($this->collectionTraitItems);
    }

    public function offsetExists($key): bool {
        if (!is_string($key) && !is_int($key)) {
            throw new Exception("Key must be a string or an integer.");
        }

        return array_key_exists($key, $this->collectionTraitItems);
    }

    public function offsetGet($key) {
        if (!$this->offsetExists($key)) {
            throw new Exception("Key: '{(string) $key}' does not exist.");
        }

        return $this->collectionTraitItems[$key];
    }

    public function offsetSet($key, $value): void {
        if (!$this->offsetExists($key)) {
            throw new Exception("Key: '{(string) $key}' does not exist.");
        }

        $this->add($value, $key);
    }

    public function offsetUnset($key): void {
        if (!$this->offsetExists($key)) {
            throw new Exception("Key: '{(string) $key}' does not exist.");
        }

        $this->remove($key);
    }

    public function add($item, $key = null): void {
        $itemsTypeHint = TypeHintFactory::tryParse($this->collectionTraitT);

        if (!$itemsTypeHint->isAssignableFrom(Type::typeOf($item))) {
            throw new Exception("Item is not assignable to the type hint.");
        }

        if ($key !== null) {
            if (!is_string($key) && !is_int($key)) {
                throw new Exception("Key must be a string or an integer.");
            }

            $this->collectionTraitItems[$key] = $item;
        } else {
            $this->collectionTraitItems[] = $item;
        }
        
    }

    public function count(?Closure $predicate = null): int {
        $funcGetArgs = func_get_args();

        if (count($funcGetArgs) > 0) {
            return $this->linqCount(...$funcGetArgs);
        }
        
        return count($this->collectionTraitItems);
    }

    public function clear(): void {
        $this->collectionTraitItems = [];
    }

    public function containsKey($key): bool {
        return $this->offsetExists($key);
    }

    public function contains($item): bool {
        return in_array($item, $this->collectionTraitItems);
    }

    public function indexOf($item) {
        if (($indexOf = array_search($item, $this->collectionTraitItems)) === false) {
            return null;
        }

        return $indexOf;
    }

    public function keys(?Closure $predicate = null): IEnumerable {
        if ($predicate === null) {
            return Enumerator::new(array_keys($this->collectionTraitItems), TypeHintFactory::keyTypes());
        }

        return $this->linqKeys($predicate);
        
    }

    public function values(?Closure $predicate = null): IEnumerable {
        if ($predicate === null) {
            return Enumerator::new(array_values($this->collectionTraitItems), TypeHintFactory::tryParse($this->collectionTraitT));
        }

        return $this->linqValues($predicate);
    }

    public function remove($key): bool {
        if (!is_string($key) && !is_int($key)) {
            throw new Exception("Key must be a string or an integer.");
        }

        if (!array_key_exists($key, $this->collectionTraitItems)) {
            return false;
        }

        unset($this->collectionTraitItems[$key]);
        
        return true;
    }
}