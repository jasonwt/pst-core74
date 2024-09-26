<?php

declare(strict_types=1);

namespace Pst\Core\Enumerable;

use Pst\Core\CoreObject;
use Pst\Core\Types\Type;
use Pst\Core\Types\ITypeHint;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Enumerable\Linq\EnumerableLinqTrait;

use Iterator;
use Generator;
use ArrayIterator;
use IteratorAggregate;

use TypeError;
use InvalidArgumentException;

class Enumerable extends CoreObject implements IteratorAggregate, IEnumerable {
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
        }

        $this->T = $T ?? TypeHintFactory::undefined();
        $this->TKey = $TKey ?? TypeHintFactory::keyTypes();

        if (!$this->TKey->isAssignableTo(TypeHintFactory::keyTypes())) {
            throw new TypeError("{$this->TKey} is not assignable to key types");
        }

        $this->iterator = $iterator;
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
            
            if ($iterable instanceof Enumerable) {    
                return $iterable;
            }
        }

        $T ??= TypeHintFactory::undefined();
        $TKey ??= TypeHintFactory::keyTypes();

        if ($T instanceof Type) {
            if ($T->isInt()) {
                return new class($iterable, $T, $TKey) extends Enumerator implements IIntegerEnumerable {};
            } else if ($T->isFloat()) {
                return new class($iterable, $T, $TKey) extends Enumerator implements INumericEnumerable {};
            } else if ($T->isString()) {
                return new class($iterable, $T, $TKey) extends Enumerator implements IStringEnumerable {};
            } else if ($T->isBool()) {
                return new class($iterable, $T, $TKey) extends Enumerator implements IBooleanEnumerable {};
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
        if (!is_numeric($start) || !is_numeric($step)) {
            throw new InvalidArgumentException("Start and step must be numeric");
        } else if ($count < 0) {
            throw new InvalidArgumentException("Count must be greater than or equal to zero");
        }

        $start = (strpos((string) $start, ".") !== false) ? (float) $start : (int) $start;
        $step = (strpos((string) $step, ".") !== false) ? (float) $step : (int) $step;

        $rangeGenerator = (function() use ($start, $count, $step): Generator {
            for ($i = 0; $i < $count; $i ++) {
                yield $start + $i * $step;
            }
        })();

        if (is_float($start) || is_float($step)) {
            return new class($rangeGenerator, Type::float()) extends Enumerator implements IFloatEnumerable {};
        }

        return new class($rangeGenerator, Type::int()) extends Enumerator implements IIntegerEnumerable {};
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
                return new class($repeatGenerator, $T, TypeHintFactory::keyTypes()) extends Enumerator implements IIntegerEnumerable {};
            } else if ($T->isFloat()) {
                return new class($repeatGenerator, $T, TypeHintFactory::keyTypes()) extends Enumerator implements INumericEnumerable {};
            } else if ($T->isString()) {
                return new class($repeatGenerator, $T, TypeHintFactory::keyTypes()) extends Enumerator implements IStringEnumerable {};
            } else if ($T->isBool()) {
                return new class($repeatGenerator, $T, TypeHintFactory::keyTypes()) extends Enumerator implements IBooleanEnumerable {};
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
    public static function linspace($start, $stop, int $num = 50, bool $endpoint = true): INumericEnumerable {
        if (!is_numeric($start) || !is_numeric($stop)) {
            throw new InvalidArgumentException("Start and stop must be numeric");
        } else if ($num < 0) {
            throw new InvalidArgumentException("Count must be greater than or equal to zero");
        }

        $start = (strpos((string) $start, ".") !== false) ? (float) $start : (int) $start;
        $stop = (strpos((string) $stop, ".") !== false) ? (float) $stop : (int) $stop;

        $linspaceGenerator = (function() use ($start, $stop, $num, $endpoint): Generator {
            $step = ($stop - $start) / ($num - ($endpoint ? 1 : 0));

            for ($i = 0; $i < $num; $i ++) {
                yield $start + $i * $step;
            }
        })();

        if (is_float($start) || is_float($stop)) {
            return new class($linspaceGenerator, Type::float()) extends Enumerator implements IFloatEnumerable {};
        }

        return new class($linspaceGenerator, Type::int()) extends Enumerator implements IIntegerEnumerable {};
    }
}