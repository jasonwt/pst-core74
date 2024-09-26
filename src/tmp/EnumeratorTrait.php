<?php

declare(strict_types=1);

namespace Pst\Core\Enumerable;

use Iterator;

/*

anything that is going to impelment a traversable should implement 

Collection implements Iterator, IEnumerable
    use EnumerableTrait
        getEnumerator(): IEnumerator
        
        T(): ITypeHint

        use EnumeratorTrait

        
    

IEnumerable
    - getEnumerator(): IEnumerator
    - T(): ITypeHint

IEnumerator
    - current()
    - key()
    - next()
    - rewind()
    - valid(): bool
    - getIterator(): Traversable



*/

trait EnumeratorTrait {
    private function current() {
        return $this->getIterator()->current();
    }

    private function key() {
        return $this->getIterator()->key();
    }

    private function next() {
        $this->getIterator()->next();
    }

    private function valid(): bool {
        return $this->getIterator()->valid();
    }

    private function rewind() {
        $this->getIterator()->rewind();
    }

    private function getIterator(): Iterator {
        while ($this->valid()) {
            yield $this->key() => $this->current();
            $this->next();
        }
    }
}