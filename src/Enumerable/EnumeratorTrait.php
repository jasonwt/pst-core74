<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Enumerable;

use Pst\Core\Types\Type;
use Pst\Core\Types\ITypeHint;
use Pst\Core\Types\TypeHintFactory;

use Iterator;
use Traversable;
use ArrayIterator;

use LogicException;
use InvalidArgumentException;

trait EnumeratorTrait {
    private static bool $validateTypes = true;

    private string $T;
    private Iterator $iterator;
    private ?ITypeHint $concreteT = null;

    /**
     * Creates a new instance of IEnumerable
     * 
     * @param iterable $iterable 
     * @param null|ITypeHint $T 
     * 
     * @return void 
     * 
     * @throws InvalidArgumentException 
     */
    protected function __construct(iterable $iterable, ?ITypeHint $T = null) {
        if (is_array($iterable)) {
            $this->iterator = new ArrayIterator($iterable);
        } else {
            $this->iterator = $iterable;
            $T ??= ($iterable instanceof IEnumerable) ? $iterable->T() : null;
        }

        $this->T = (string) ($T ?? TypeHintFactory::undefined());

        if ($this->T === "void") {
            throw new InvalidArgumentException("Type hint cannot be void");
        }
    }

    /**
     * Gets the type hint
     * 
     * @return ITypeHint 
     */
    public function T(): ITypeHint {
        return TypeHintFactory::tryParse($this->T);
    }

    /**
     * Gets the current element
     * 
     * @return mixed 
     */
    public function current() {
        if (!$this->valid()) {
            throw new LogicException("The enumerator is not valid.");
        }
        
        $currentItem = $this->iterator->current();

        if (static::$validateTypes) {
            $this->concreteT ??= TypeHintFactory::tryParse($this->T);

            if (!$this->concreteT->isAssignableFrom(Type::typeOf($currentItem))) {
                throw new InvalidArgumentException("Value of type " . gettype($currentItem) . " is not assignable to source type " . $this->T);
            }
        }

        return $currentItem;
    }

    /**
     * Gets the current key
     * 
     * @return mixed 
     */
    public function key() {
        return $this->iterator->key();
    }

    /**
     * Moves the iterator to the next element
     * 
     * @return void 
     */
    public function next() {
        $this->iterator->next();
    }

    /**
     * Rewinds the iterator
     * 
     * @return void 
     */
    private function rewind() {
        $this->iterator->rewind();
    }

    /**
     * Checks if the iterator is valid
     * 
     * @return bool 
     */
    public function valid(): bool {
        return $this->iterator->valid();
    }

    /**
     * Gets the iterator
     * 
     * @return Traversable
     * 
     * @throws InvalidArgumentException 
     * @throws LogicException 
     */
    private function getIterator(): Traversable {
        while ($this->valid()) {
            yield $this->key() => $this->current();
            $this->next();
        }

        $this->concreteT = null;
    }

    /**
     * Creates a new instance of IImmutableEnumerable from the current instance
     * 
     * @param iterable $iterable 
     * @param null|ITypeHint $T 
     * 
     * @return IEnumerable 
     */
    public function toImmutableEnumerator(): IImmutableEnumerable {
        return ImmutableEnumerator::new(iterator_to_array($this), $this->T());
    }
}