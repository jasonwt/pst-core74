<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Enumerable;

use Pst\Core\Types\ITypeHint;
use Pst\Core\Types\TypeHintFactory;

use Generator;

use InvalidArgumentException;
use Iterator;

class Enumerator implements Iterator, IImmutableEnumerable {
    use ImmutableEnumerableLinqTrait {
        linqCount as public count;
        linqKeys as public keys;
        linqValues as public values;
    }

    use EnumeratorTrait {
        current as public;
        key as public;
        next as public;
        valid as public;
        rewind as public;
    }

    /**
     * Creates a new instance of IImmutableEnumerable from an iterable object
     * 
     * @param iterable $iterable 
     * @param null|ITypeHint $T 
     * 
     * @return IImmutableEnumerable 
     */
    public static function new(iterable $iterable, ?ITypeHint $T = null): IImmutableEnumerable {
        if ($iterable instanceof IImmutableEnumerable) {
            if ($T === null || $T->isAssignableFrom($iterable->T())) {
                return $iterable;
            } else {
                return new static($iterable, $T);
            }

        } else if ($iterable instanceof IToImmutableEnumerable) {
            return $iterable->toImmutableEnumerable();

        }

        return new static($iterable, $T);
    }

    /**
     * Creates a new instance of IImmutableEnumerable with no elements
     * 
     * @param array $array 
     * @param null|ITypeHint $T 
     * 
     * @return IImmutableEnumerable 
     */
    public static function empty(?ITypeHint $T = null): IImmutableEnumerable {
        return new static([], $T);
    }

    /**
     * Creates an new instance of IImmutableEnumerable from a range
     * 
     * @param float|int $start 
     * @param int $count 
     * @param float|int $step 
     * 
     * @return IImmutableEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public static function range($start, int $count, $step = 1): IImmutableEnumerable {
        if (!is_numeric($start) || !is_numeric($step)) {
            throw new InvalidArgumentException("Start and step must be numeric");
        } else if ($count < 0) {
            throw new InvalidArgumentException("Count must be greater than or equal to zero");
        }

        $start = (strpos((string) $start, ".") !== false) ? (float) $start : (int) $start;
        $step = (strpos((string) $step, ".") !== false) ? (float) $step : (int) $step;

        return new static((function() use ($start, $count, $step): Generator {
            for ($i = 0; $i < $count; $i ++) {
                yield $start + $i * $step;
            }
        })(), TypeHintFactory::tryParse("int|float"));
    }

    /**
     * Creates a new instance of IImmutableEnumerable from a repeat value
     * 
     * @param mixed $value 
     * @param int $count 
     * 
     * @return IImmutableEnumerable 
     */
    public static function repeat($value, int $count): IImmutableEnumerable {
        return new static((function() use ($value, $count): Generator {
            for ($i = 0; $i < $count; $i ++) {
                yield $value;
            }
        })(), TypeHintFactory::tryParse("mixed"));
    }

    /**
     * Creates a new instance of IImmutableEnumerable from a linspace
     * 
     * @param float|int $start 
     * @param float|int $stop 
     * @param int $num 
     * @param bool $endpoint 
     * 
     * @return IImmutableEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public static function linspace($start, $stop, int $num = 50, bool $endpoint = true): IImmutableEnumerable {
        if (!is_numeric($start) || !is_numeric($stop)) {
            throw new InvalidArgumentException("Start and stop must be numeric");
        } else if ($num < 0) {
            throw new InvalidArgumentException("Count must be greater than or equal to zero");
        }

        $start = (strpos((string) $start, ".") !== false) ? (float) $start : (int) $start;
        $stop = (strpos((string) $stop, ".") !== false) ? (float) $stop : (int) $stop;

        return new static((function() use ($start, $stop, $num, $endpoint): Generator {
            $step = ($stop - $start) / ($num - ($endpoint ? 1 : 0));

            for ($i = 0; $i < $num; $i ++) {
                yield $start + $i * $step;
            }
        })(), TypeHintFactory::tryParse("int|float"));
    }
}