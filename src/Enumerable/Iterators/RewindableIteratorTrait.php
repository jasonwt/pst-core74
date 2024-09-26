<?php

declare(strict_types=1);

namespace Pst\Core\Enumerable\Iterators;

use ArrayIterator;
use Generator;
use Iterator;
use IteratorAggregate;
use Traversable;

trait RewindableIteratorTrait {
    private ?Iterator $iterator = null;

    private $lazyLoading = true;
    private $currentKey = null;
    private int $currentKeyIndex = 0;
    private array $keyValues = [];

    public function __construct(iterable $iterable, bool $lazyLoading = true) {
        if (is_array($iterable)) {
            $this->keyValues = $iterable;
            $this->currentKey = array_key_first($this->keyValues) ?? null;
        } else if ($iterable instanceof ArrayIterator || !$lazyLoading) {
            /** @var Traversable $iterable */
            $this->keyValues = iterator_to_array($iterable, true);
            $this->currentKey = array_key_first($this->keyValues) ?? null;
        } else {
            $this->iterator = ($iterable instanceof IteratorAggregate) ? $iterable->getIterator() : $iterable;
            $this->currentKey = $this->populateNext();
            
        }
    }

    /**
     * Populate the next element
     * 
     * @return mixed 
     */
    private function populateNext() {
        if ($this->iterator !== null) {
            if (!$this->iterator->valid()) {
                return ($this->iterator = null);
            }

            $key = $this->iterator->key();

            $this->keyValues[$key] = $this->iterator->current();
            $this->iterator->next();
    
            return $key;
        }

        return null;
    }

    /**
     * Populate the remaining elements
     * 
     * @return void 
     */
    private function populateRemaining() {
        if ($this->iterator === null) {
            return;
        }

        while ($this->populateNext() !== null) {
            continue;
        }
    }

    /**
     * Get the current element
     * 
     * @return mixed 
     */
    public function current() {
        return $this->keyValues[$this->currentKey] ?? null;
    }

    /**
     * Get the current key
     * 
     * @return mixed 
     */
    public function key() {
        return $this->currentKey;
    }

    /**
     * Move the iterator to the next element
     * 
     * @return void 
     */
    public function next(): void {
        $this->currentKeyIndex++;

        if ($this->currentKeyIndex >= count($this->keyValues)) {
            $this->currentKey = $this->populateNext();
        } else {
            $newKeyValues = array_slice($this->keyValues, $this->currentKeyIndex, 1, true); // Preserve keys
            $this->currentKey = key($newKeyValues);
        }
    }

    /**
     * Rewind the iterator
     * 
     * @return void 
     */
    public function rewind(): void {
        $this->currentKey = array_key_first($this->keyValues) ?? null;
        $this->currentKeyIndex = 0;
    }

    /**
     * Check if the iterator is valid
     * 
     * @return bool 
     */
    public function valid(): bool {
        return $this->currentKey !== null;
    }

    /**
     * Get the iterator
     * 
     * @return Iterator 
     */
    public function getIterator(): Iterator {
        if ($this->iterator === null) {
            return (function(): Generator {
                foreach ($this->keyValues as $key => $value) {
                    yield $key => $value;
                }
            })();
        } else {
            return (function(): Generator {
                $currentKeyIndex = 0;

                if (($currentKey = array_key_first($this->keyValues) ?? $this->populateNext()) === null) {
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