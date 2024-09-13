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

use Exception;
use Traversable;
use ArrayIterator;
use Closure;
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
    }

    private string $CollectionTrait_itemsTypeHint;
    private array $CollectionTrait_items = [];

    public function __construct($iterable, ?ITypeHint $T = null) {
        if (is_array($iterable)) {
            $iterable = new ArrayIterator($iterable);
            $T ??= TypeHintFactory::undefined();
        } else if ($iterable instanceof IEnumerable) {
            if ($T !== null && $T !== $iterable->T()) {
                throw new InvalidArgumentException("Type hint mismatch");
            }

            $T = $iterable->T();
        } else if ($iterable instanceof Traversable) {
            $T ??= TypeHintFactory::undefined();
        } else {
            throw new InvalidArgumentException("iterable argument must implement ICollection, Traversable or be iterable.");
        }

        $this->CollectionTrait_itemsTypeHint = (string) ($T ?? TypeHintFactory::mixed());
        $this->CollectionTrait_items = [];

        foreach ($iterable as $key => $value) {
            $this->add($value, $key);
        }
    }

    public function T(): ITypeHint {
        return TypeHintFactory::tryParse($this->CollectionTrait_itemsTypeHint);
    }

    public function getIterator(): Traversable {
        return new ArrayIterator($this->CollectionTrait_items);
    }

    public function offsetExists(mixed $key): bool {
        if (!is_string($key) && !is_int($key)) {
            throw new Exception("Key must be a string or an integer.");
        }

        return array_key_exists($key, $this->CollectionTrait_items);
    }

    public function offsetGet($key) {
        if (!$this->offsetExists($key)) {
            throw new Exception("Key: '{(string) $key}' does not exist.");
        }

        return $this->CollectionTrait_items[$key];
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
        $itemsTypeHint = TypeHintFactory::tryParse($this->CollectionTrait_itemsTypeHint);

        if (!$itemsTypeHint->isAssignableFrom(Type::fromValue($item))) {
            throw new Exception("Item is not assignable to the type hint.");
        }

        if ($key !== null) {
            if (!is_string($key) && !is_int($key)) {
                throw new Exception("Key must be a string or an integer.");
            }

            $this->CollectionTrait_items[$key] = $item;
        } else {
            $this->CollectionTrait_items[] = $item;
        }
        
    }

    public function count(?Closure $predicate = null): int {
        $funcGetArgs = func_get_args();

        if (count($funcGetArgs) > 0) {
            return $this->linqCount(...$funcGetArgs);
        }
        
        return count($this->CollectionTrait_items);
    }

    public function clear(): void {
        $this->CollectionTrait_items = [];
    }

    public function containsKey($key): bool {
        return $this->offsetExists($key);
    }

    public function contains($item): bool {
        return in_array($item, $this->CollectionTrait_items);
    }

    public function indexOf($item) {
        if (($indexOf = array_search($item, $this->CollectionTrait_items)) === false) {
            return null;
        }

        return $indexOf;
    }

    public function keys(?Closure $predicate = null): IEnumerable {
        if ($predicate === null) {
            return Enumerator::new(array_keys($this->CollectionTrait_items), TypeHint::keyTypes());
        }

        return $this->linqKeys($predicate);
        
    }

    public function values(): IEnumerable {
        return new ReadOnlyCollection(array_values($this->CollectionTrait_items), TypeHintFactory::tryParse($this->CollectionTrait_itemsTypeHint));
    }

    public function remove($key): bool {
        if (!is_string($key) && !is_int($key)) {
            throw new Exception("Key must be a string or an integer.");
        }

        if (!array_key_exists($key, $this->CollectionTrait_items)) {
            return false;
        }

        unset($this->CollectionTrait_items[$key]);
        
        return true;
    }
}