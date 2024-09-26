<?php

declare(strict_types=1);

namespace Pst\Core\Enumerable;

use Pst\Core\CoreObject;
use Pst\Core\Types\Type;
use Pst\Core\Types\ITypeHint;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Enumerable\Linq\EnumerableLinqTrait;
use Pst\Core\Enumerable\Iterators\RewindableIterator;

use Iterator;
use ArrayIterator;
use CachingIterator;
use IteratorAggregate;

use TypeError;
use InvalidArgumentException;
use Pst\Core\Enumerable\Iterators\IRewindableIterator;

class RewindableEnumerable extends CoreObject implements IteratorAggregate, IRewindableEnumerable {
    use EnumerableLinqTrait {}

    private ITypeHint $T;
    private ITypeHint $TKey;
    private Iterator $iterator;

    /**
     * Creates a new instance of Enumerable
     * 
     * @param iterable $iterator 
     * @param ITypeHint|null $T 
     * 
     * @throws TypeError 
     * @throws InvalidArgumentException 
     */
    private function __construct(iterable $iterator, ?ITypeHint $T = null, ?ITypeHint $TKey = null) {
        if (is_array($iterator)) {
            $this->iterator = new ArrayIterator($iterator);
        } else {
            if ($iterator instanceof IEnumerable) {
                $T ??= $iterator->T();
                $TKey ??= $iterator->TKey();
            }

            while ($iterator instanceof IteratorAggregate) {
                $iterator = $iterator->getIterator();
            }

            // there are more then this.  BUG, TODO, FIX
            $isRewindable = 
                $iterator instanceof ArrayIterator ||
                $iterator instanceof CachingIterator ||
                $iterator instanceof IRewindableIterator ||
                $iterator instanceof IRewindableEnumerable;

            if (!$isRewindable) {
                $iterator = new RewindableIterator($iterator);
            }

            $this->iterator = $iterator;
        }

        $this->T = $T ?? TypeHintFactory::undefined();
        $this->TKey = $TKey ?? TypeHintFactory::keyTypes();

        if (!$this->TKey->isAssignableTo(TypeHintFactory::keyTypes())) {
            throw new TypeError("{$this->TKey} is not assignable to key types");
        }
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
     * Determines if the enumerable is rewindable
     * 
     * @return bool 
     */
    public function isRewindable(): bool {
        return true;
    }

    /**
     * Rewinds the iterator
     * 
     * @return void 
     */
    public function rewind(): void {
        $this->iterator->rewind();
    }
    
    /**
     * Gets the iterator
     * 
     * @return Iterator 
     * 
     * @throws TypeError 
     */
    public function getIterator(): Iterator {
        return $this->iterator;
    }

    ////////////////////////////////////////////// PUBLIC FACTORY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /**
     * Creates an enumerable from an iterable
     * 
     * @param iterable $iterable 
     * @param ITypeHint|null $T 
     * @param ITypeHint|null $TKey 
     * 
     * @return IEnumerable 
     */
    public static function create(iterable $iterable, ?ITypeHint $T = null, ?ITypeHint $TKey = null): IEnumerable {
        if (!is_array($iterable) && $iterable instanceof IEnumerable) {
            $iterableT = $iterable->T();
            $iterableTKey = $iterable->TKey();

            if ($T === null) {
                $T = $iterableT;
            } else if (!$T->isAssignableFrom($iterableT)) {
                throw new TypeError("{$T} is not assignable to {$iterableT}");
            }

            if ($TKey === null) {
                $TKey = $iterableTKey;
            } else if (!$TKey->isAssignableFrom($iterableTKey)) {
                throw new TypeError("{$TKey} is not assignable to {$iterableTKey}");
            }
            
            if ($iterable instanceof RewindableIterator) {    
                return $iterable;
            }
        }

        $T ??= TypeHintFactory::undefined();
        $TKey ??= TypeHintFactory::keyTypes();

        if ($T instanceof Type) {
            if ($T->isInt()) {
                return new class($iterable, $T, $TKey) extends RewindableEnumerable implements IIntegerEnumerable, IRewindableEnumerable {};
            } else if ($T->isFloat()) {
                return new class($iterable, $T, $TKey) extends RewindableEnumerable implements IFloatEnumerable, IRewindableEnumerable {};
            } else if ($T->isString()) {
                return new class($iterable, $T, $TKey) extends RewindableEnumerable implements IStringEnumerable, IRewindableEnumerable {};
            } else if ($T->isBool()) {
                return new class($iterable, $T, $TKey) extends RewindableEnumerable implements IBooleanEnumerable, IRewindableEnumerable {};
            }
        }

        return new static($iterable, $T, $TKey);
    }

    /**
     * Creates an empty enumerable
     * 
     * @param ITypeHint|null $T 
     * 
     * @return IEnumerable 
     */
    public static function empty(?ITypeHint $T = null): IRewindableEnumerable {
        return new static([], $T);
    }
}