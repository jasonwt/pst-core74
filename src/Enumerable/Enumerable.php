<?php

declare(strict_types=1);

namespace Pst\Core\Enumerable;

use Pst\Core\CoreObject;
use Pst\Core\Types\Type;
use Pst\Core\Types\ITypeHint;
use Pst\Core\Types\TypeUnion;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Traits\ShouldTypeCheckTrait;
use Pst\Core\Enumerable\Linq\EnumerableLinqTrait;
use Pst\Core\Enumerable\Iterators\IRewindableIterator;

use Iterator;
use Generator;
use ArrayIterator;
use CachingIterator;
use IteratorAggregate;

use TypeError;
use InvalidArgumentException;

class Enumerable extends CoreObject implements IteratorAggregate, IEnumerable {
    use ShouldTypeCheckTrait;

    use EnumerableLinqTrait {}

    private ITypeHint $T;
    private ITypeHint $TKey;
    private bool $isRewindable = false;
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

                if (!$T->isAssignableFrom($iterator->T())) {
                    throw new TypeError("{$T} is not assignable to {$iterator->T()}");
                }

                if (!$TKey->isAssignableFrom($iterator->TKey())) {
                    throw new TypeError("{$TKey} is not assignable to {$iterator->TKey()}");
                }
            }

            while ($iterator instanceof IteratorAggregate) {
                $iterator = $iterator->getIterator();
            }

            $this->iterator = $iterator;
        }

        // there are more then this.  BUG, TODO, FIX
        $this->isRewindable = 
            $this->iterator instanceof ArrayIterator ||
            $this->iterator instanceof CachingIterator ||
            $this->iterator instanceof IRewindableIterator ||
            $this->iterator instanceof IRewindableEnumerable;

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
        return $this->isRewindable;
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

    ////////////////////////////////////////////// PUBLIC STATIC METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    public static function isIterableRewindable(iterable $iterable): bool {
        return 
            $iterable instanceof ArrayIterator ||
            $iterable instanceof CachingIterator ||
            $iterable instanceof IRewindableIterator ||
            $iterable instanceof IRewindableEnumerable;
    }
    /**
     * Determines the type hint of the values in an iterable
     * 
     * @param iterable $iterable 
     * 
     * @return ITypeHint 
     */
    public static function determineTypeHint(iterable $iterable): ITypeHint {
        $types = [];

        foreach ($iterable as $value) {
            $type = Type::typeOf($value);

            $types[$type->fullName()] ??= $type;
        }

        if (count($types) === 1) {
            return $types[array_key_first($types)];
        }

        return TypeUnion::create(...array_values($types));
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
                throw new TypeError("{$T} is not assignable from {$iterableT}");
            }

            if ($TKey === null) {
                $TKey = $iterableTKey;
            } else if (!$TKey->isAssignableFrom($iterableTKey)) {
                throw new TypeError("{$TKey} is not assignable from {$iterableTKey}");
            }
            
            if ($iterable instanceof Enumerable) {    
                return $iterable;
            }
        }

        $T ??= TypeHintFactory::undefined();
        $TKey ??= TypeHintFactory::keyTypes();

        if ($T instanceof Type) {
            if ($T->isInt()) {
                return new class($iterable, $T, $TKey) extends Enumerable implements IIntegerEnumerable {};
            } else if ($T->isFloat()) {
                return new class($iterable, $T, $TKey) extends Enumerable implements IFloatEnumerable {};
            } else if ($T->isString()) {
                return new class($iterable, $T, $TKey) extends Enumerable implements IStringEnumerable {};
            } else if ($T->isBool()) {
                return new class($iterable, $T, $TKey) extends Enumerable implements IBooleanEnumerable {};
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
    public static function empty(?ITypeHint $T = null): IEnumerable {
        return new static([], $T);
    }

    /**
     * Creates an enumerable from a range
     * 
     * @param int|float $start 
     * @param int $count 
     * @param int|float $step 
     * 
     * @return INumericEnumerable 
     */
    public static function range($start, int $count, $step = 1): INumericEnumerable {
        if (!is_int($start) && !is_float($start)) {
            throw new InvalidArgumentException("Start must be an integer or float");
        } else if (!is_int($step) && !is_float($step)) {
            throw new InvalidArgumentException("Step must be an integer or float");
        } else if ($count < 0) {
            throw new InvalidArgumentException("Count must be greater than or equal to zero");
        }
        
        $rangeGenerator = (function() use ($start, $count, $step): Generator {
            for ($i = 0; $i < $count; $i ++) {
                yield $start + $i * $step;
            }
        })();

        if (is_float($start) || is_float($step)) {
            return new class($rangeGenerator, Type::float()) extends Enumerable implements IFloatEnumerable {};
        }

        return new class($rangeGenerator, Type::int()) extends Enumerable implements IIntegerEnumerable {};
    }

    /**
     * Creates an enumerable from a repeat
     * 
     * @param mixed $value 
     * @param int $count 
     * 
     * @return IEnumerable 
     */
    public static function repeat($value, int $count): IEnumerable {
        $T = Type::typeOf($value);

        $repeatGenerator = (function() use ($value, $count): Generator {
            for ($i = 0; $i < $count; $i ++) {
                yield $value;
            }
        })();

        if ($T instanceof Type) {
            if ($T->isInt()) {
                return new class($repeatGenerator, $T, TypeHintFactory::keyTypes()) extends Enumerable implements IIntegerEnumerable {};
            } else if ($T->isFloat()) {
                return new class($repeatGenerator, $T, TypeHintFactory::keyTypes()) extends Enumerable implements IFloatEnumerable {};
            } else if ($T->isString()) {
                return new class($repeatGenerator, $T, TypeHintFactory::keyTypes()) extends Enumerable implements IStringEnumerable {};
            } else if ($T->isBool()) {
                return new class($repeatGenerator, $T, TypeHintFactory::keyTypes()) extends Enumerable implements IBooleanEnumerable {};
            }
        }

        return new static($repeatGenerator, $T, TypeHintFactory::keyTypes());
    }

    /**
     * Creates an enumerable from a linspace
     * 
     * @param int|float $start 
     * @param int|float $stop 
     * @param int $num 
     * @param bool $endpoint 
     * 
     * @return INumericEnumerable 
     */
    public static function linspace($start, $stop, int $num, bool $endpoint = true): INumericEnumerable {
        if (!is_int($start) && !is_float($start)) {
            throw new InvalidArgumentException("Start must be an integer or float");
        } else if (!is_int($stop) && !is_float($stop)) {
            throw new InvalidArgumentException("Stop must be an integer or float");
        } else if ($num <= 1) {
            throw new InvalidArgumentException("num must be greater than one");
        }

        if ($start === $stop && $num) {
            throw new InvalidArgumentException("Start and stop must be different");
        }

        $direction = ($start < $stop) ? 1 : -1;
        $absStartMinusStop = abs($start - $stop);

        $linspaceGenerator = (function() use ($start, $stop, $num, $direction, $endpoint): Generator {
            $step = (($stop - $start) / ($num - ($endpoint ? 1 : 0))) * $direction;

            for ($i = 0; $i < $num; $i ++) {
                yield $start + $i * $step;
            }
        })();

        if (is_float($start) || is_float($stop) || $absStartMinusStop < ($num - ($endpoint ? 1 : 0))) {
            return new class($linspaceGenerator, Type::float()) extends Enumerable implements IFloatEnumerable {};
        }

        return new class($linspaceGenerator, Type::int()) extends Enumerable implements IIntegerEnumerable {};
    }
}