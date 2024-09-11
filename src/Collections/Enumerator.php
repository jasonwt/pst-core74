<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\Types\Type;
use Pst\Core\Types\TypeHint;
use Pst\Core\Types\ITypeHint;

use Pst\Core\Collections\Traits\LinqTrait;

use Generator;
use Traversable;
use ArrayIterator;

use InvalidArgumentException;
use LogicException;

class Enumerator implements IEnumerable {
    use LinqTrait;

    private string $T;
    private Traversable $iterator;

    /**
     * Creates a new instance of Enumerator
     * 
     * @param iterable|Traversable $iterator 
     * @param null|ITypeHint $T 
     * 
     * @return void 
     * 
     * @throws InvalidArgumentException 
     */
    protected function __construct($iterator, ?ITypeHint $T = null) {
        if (is_array($iterator)) {
            $iterator = new ArrayIterator($iterator);
            $T ??= TypeHint::undefined();
        } else if ($iterator instanceof IEnumerable) {
            if ($T !== null && $T !== $iterator->T()) {
                throw new InvalidArgumentException("Type hint mismatch");
            } else {
                $T = $iterator->T();
            }
        } else if ($iterator instanceof Traversable) {
            $T ??= TypeHint::undefined();
        } else {
            throw new InvalidArgumentException("iterator argument must implement IEnumerable, Traversable or be iterable.");
        }

        $this->iterator = $iterator;

        $this->T = (string) ($T ?? TypeHint::mixed());

        if ($this->T === "void") {
            throw new InvalidArgumentException("Type hint cannot be void");
        }
    }

    /**
     * Gets the iterator
     * 
     * @return Traversable<mixed, mixed>|mixed[] 
     * 
     * @throws InvalidArgumentException 
     * @throws LogicException 
     */
    public function getIterator(): Traversable {
        $T = TypeHint::fromTypeNames($this->T);

        foreach ($this->iterator as $key => $value) {
            if (!$T->isAssignableFrom(Type::fromValue($value))) {
                throw new InvalidArgumentException("Value of type " . gettype($value) . " is not assignable to source type " . $this->T);
            }

            yield $key => $value;
        }
    }

    /**
     * Gets the type hint
     * 
     * @return ITypeHint 
     */
    public function T(): ITypeHint {
        return TypeHint::fromTypeNames($this->T);
    }

    /**
     * Creates a new instance of Enumerator from an iterable object
     * 
     * @param iterable $iterable 
     * @param null|ITypeHint $T 
     * 
     * @return Enumerator 
     */
    public static function new(iterable $iterable, ?ITypeHint $T = null): Enumerator {
        return new Enumerator($iterable, $T);
    }

    /**
     * Creates an new instance of Enumerator from a range
     * 
     * @param float|int $start 
     * @param int $count 
     * @param float|int $step 
     * 
     * @return Enumerator 
     * 
     * @throws InvalidArgumentException 
     */
    public static function range($start, int $count, $step = 1): Enumerator {
        if (!is_numeric($start) || !is_numeric($step)) {
            throw new InvalidArgumentException("Start and step must be numeric");
        } else if ($count < 0) {
            throw new InvalidArgumentException("Count must be greater than or equal to zero");
        }

        $start = (strpos((string) $start, ".") !== false) ? (float) $start : (int) $start;
        $step = (strpos((string) $step, ".") !== false) ? (float) $step : (int) $step;

        return new Enumerator((function() use ($start, $count, $step): Generator {
            for ($i = 0; $i < $count; $i ++) {
                yield $start + $i * $step;
            }
        })(), TypeHint::fromTypeNames("int|float"));
    }

    /**
     * Creates a new instance of Enumerator from a repeat value
     * 
     * @param mixed $value 
     * @param int $count 
     * 
     * @return Enumerator 
     */
    public static function repeat($value, int $count): Enumerator {
        return new Enumerator((function() use ($value, $count): Generator {
            for ($i = 0; $i < $count; $i ++) {
                yield $value;
            }
        })(), TypeHint::fromTypeNames("mixed"));
    }

    /**
     * Creates a new instance of Enumerator from a linspace
     * 
     * @param float|int $start 
     * @param float|int $stop 
     * @param int $num 
     * @param bool $endpoint 
     * 
     * @return Enumerator 
     * 
     * @throws InvalidArgumentException 
     */
    public static function linspace($start, $stop, int $num = 50, bool $endpoint = true): Enumerator {
        if (!is_numeric($start) || !is_numeric($stop)) {
            throw new InvalidArgumentException("Start and stop must be numeric");
        } else if ($num < 0) {
            throw new InvalidArgumentException("Count must be greater than or equal to zero");
        }

        $start = (strpos((string) $start, ".") !== false) ? (float) $start : (int) $start;
        $stop = (strpos((string) $stop, ".") !== false) ? (float) $stop : (int) $stop;

        return new Enumerator((function() use ($start, $stop, $num, $endpoint): Generator {
            $step = ($stop - $start) / ($num - ($endpoint ? 1 : 0));

            for ($i = 0; $i < $num; $i ++) {
                yield $start + $i * $step;
            }
        })(), TypeHint::fromTypeNames("int|float"));
    }
}