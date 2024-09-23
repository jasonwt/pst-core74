<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\Types\Type;
use Pst\Core\Types\TypeHintFactory;

use Exception;
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
    use ReadonlyCollectionTrait;
    
    public function offsetSet($key, $value): void {
        if (!$this->offsetExists($key)) {
            throw new Exception("Key: '{(string) $key}' does not exist.");
        }

        $this->collectionTraitItems[$key] = $value;
    }

    public function offsetUnset($key): void {
        if (!$this->offsetExists($key)) {
            throw new Exception("Key: '{(string) $key}' does not exist.");
        }

        $this->remove($key);
    }

    public function tryAdd($item, $key = null): bool {
        $itemsTypeHint = TypeHintFactory::tryParse($this->collectionTraitT);

        if (!$itemsTypeHint->isAssignableFrom(Type::typeOf($item))) {
            throw new Exception("Item is not assignable to the type hint.");
        }

        if ($key !== null) {
            if (!is_string($key) && !is_int($key)) {
                throw new Exception("Key must be a string or an integer.");
            }

            if ($this->offsetExists($key)) {
                return false;
            }

            $this->collectionTraitItems[$key] = $item;
        } else {
            $this->collectionTraitItems[] = $item;
        }

        return true;
    }

    public function add($item, $key = null): void {
        if (!$this->tryAdd($item, $key)) {
            throw new InvalidArgumentException("Key: '{(string) $key}' already exists.");
        }
    }

    public function clear(): void {
        $this->collectionTraitItems = [];
        $this->collectionTraitItemsIterator = null;
    }

    public function remove($key): bool {
        if (!is_string($key) && !is_int($key)) {
            throw new Exception("Key must be a string or an integer.");
        }

        if ($this->offsetExists($key)) {
            unset($this->collectionTraitItems[$key]);
            return true;
        }

        while (($next = $this->populateNext()) !== null) {
            if ($next[0] === $key) {
                unset($this->collectionTraitItems[$key]);
                return true;
            }
        }

        return false;
    }
}