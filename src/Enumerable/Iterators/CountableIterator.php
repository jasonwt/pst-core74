<?php

declare(strict_types=1);

namespace Pst\Core\Enumerable\Iterators;

use Pst\Core\CoreObject;
use Pst\Core\Interfaces\ICoreObject;

use Iterator;
use Countable;
use Generator;
use ArrayIterator;

use RuntimeException;
use IteratorAggregate;

class CountableIterator extends CoreObject implements ICoreObject, Iterator, Countable {
    private ?Iterator $source = null;
    private ?int $count = null;
    private int $iterationCount = 0;

    public function __construct(iterable $source) {
        if (is_array($source)) {
            $this->count = count($source);
            $this->source = new ArrayIterator($source);
        } else {
            while ($source instanceof IteratorAggregate) {
                $source = $source->getIterator();
            }

            $this->source = $source;
        }
    }

    private function createGenerator(array $values): Generator {
        foreach ($values as $key => $value) {
            yield $key => $value;
        }
    }

    public function count(): int {
        if ($this->count !== null) {
            return $this->count;
        }

        if ($this->source === null) {
            $this->count ??= $this->iterationCount;
            
        } else {
            $remainingValues = [];

            while ($this->source->valid()) {
                $remainingValues[$this->source->key()] = $this->source->current();
                $this->source->next();
            }

            $this->count = $this->iterationCount + count($remainingValues);
            $this->source = $this->createGenerator($remainingValues);
        }

        return $this->count;
    }

    public function current() {
        if (!$this->source) {
            return null;
        }

        return $this->source->current();
    }

    public function key() {
        if (!$this->source) {
            return null;
        }

        return $this->source->key();
    }

    public function next(): void {
        if ($this->source === null) {
            return;
        }

        $this->iterationCount ++;

        $this->source->next();
    }

    public function rewind(): void {
        if ($this->source === null) {
            throw new RuntimeException("Cannot rewind the iterator as it is not valid or rewindable.");
        }

        $this->iterationCount = 0;
        $this->source->rewind();
    }

    public function valid(): bool {
        if ($this->source === null) {
            return false;
        }

        if ($this->source->valid()) {
            return true;
        }

        $this->source = null;

        return false;
    }
}