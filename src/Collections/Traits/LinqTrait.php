<?php

declare(strict_types=1);

namespace Pst\Core\Collections\Traits;


use Pst\Core\Func;
use Pst\Core\Comparer;
use Pst\Core\IComparer;
use Pst\Core\IToString;
use Pst\Core\EqualityComparer;
use Pst\Core\IEqualityComparer;
use Pst\Core\Types\Type;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Types\ITypeHint;
use Pst\Core\Collections\Enumerator;
use Pst\Core\Collections\IEnumerable;
use Pst\Core\Collections\Collection;
use Pst\Core\Collections\ICollection;

use Pst\Core\Exceptions\NotImplementedException;
use Pst\Core\Exceptions\InvalidOperationException;

use Closure;
use Generator;
use InvalidArgumentException;

use Traversable;

trait LinqTrait {
    public abstract function T(): ITypeHint;

    // aggregate

    /**
     * Determines whether all elements of a sequence satisfy a condition
     * 
     * @param Closure $predicate 
     * 
     * @return bool 
     * 
     * @throws InvalidArgumentException 
     */
    public function all(Closure $predicate): bool {
        $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::keyTypes(), Type::bool());

        foreach ($this as $key => $value) {
            if (!$predicateFunc($value, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determines whether any element of a sequence satisfies a condition
     * 
     * @param Closure $predicate 
     * 
     * @return bool 
     * 
     * @throws InvalidArgumentException 
     */
    public function any(Closure $predicate): bool {
        $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::keyTypes(), Type::bool());

        foreach ($this as $key => $value) {
            if ($predicateFunc($value, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the number of elements in a sequence
     * 
     * @param Closure|null $predicate 
     * 
     * @return int 
     * 
     * @throws InvalidArgumentException 
     */
    public function count(?Closure $predicate = null): int {
        if ($predicate === null) {
            return iterator_count($this);
        }

        $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::keyTypes(), Type::bool());
        $count = 0;

        foreach ($this as $key => $value) {
            if ($predicateFunc($value, $key)) {
                $count ++;
            }
        }

        return $count;
    }

    /**
     * Returns distinct elements from a sequence
     * 
     * @param null|Closure $predicate 
     * 
     * @return mixed 
     * 
     * @throws InvalidOperationException 
     * @throws InvalidArgumentException 
     */
    public function first(?Closure $predicate = null) {
        if ($predicate === null) {
            foreach ($this as $value) {
                return $value;
            }

            throw new InvalidOperationException("Sequence contains no elements");
        }

        $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::keyTypes(), Type::bool());

        foreach ($this as $key => $value) {
            if ($predicateFunc($value, $key)) {
                return $value;
            }
        }

        throw new InvalidOperationException("No element satisfies the condition");
    }


    /**
     * Returns the keys of a sequence as an IEnumerable
     * 
     * @param null|Closure $predicate
     * 
     * @return IEnumerable
     */
    public function keys(?Closure $predicate = null): IEnumerable {
        if ($predicate === null) {
            return Enumerator::new((function(): Generator {
                foreach ($this as $key => $value) {
                    yield $key;
                }
            })(), TypeHintFactory::keyTypes());
        }

        $predicate = Func::new($predicate, $this->T(), TypeHintFactory::keyTypes(), Type::bool());

        return Enumerator::new((function() use ($predicate): Generator {
            foreach ($this as $key => $value) {
                if ($predicate($value, $key)) {
                    yield $key;
                }
            }
        })(), TypeHintFactory::keyTypes());
    }

    /**
     * Concatenates the members of a collection, using the specified separator between each member
     * 
     * @param string $separator 
     * 
     * @return string 
     * 
     * @throws InvalidArgumentException
     */
    public function join(string $separator = ""): string {
        $stringValues = array_map(function($value) {
            if (is_string($value)) {
                return $value;
            } else if (is_int($value) || is_float($value)) {
                return (string) $value;
            } else if (!$value instanceof IToString) {
                throw new InvalidArgumentException("Value must be a string, integer, float or implement IToString");
            }

            return $value->toString();
        }, $this->toArray());
        
        return implode($separator, $stringValues);
    }

    /**
     * Returns the last element of a sequence
     * 
     * @param null|Closure $predicate 
     * 
     * @return mixed 
     * 
     * @throws InvalidOperationException 
     * @throws InvalidArgumentException 
     */
    public function last(?Closure $predicate = null) {
        if ($predicate === null) {
            $last = null;

            foreach ($this as $value) {
                $last = $value;
            }

            if ($last === null) {
                throw new InvalidOperationException("Sequence contains no elements");
            }

            return $last;
        }

        $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::keyTypes(), Type::bool());
        $last = null;

        foreach ($this as $key => $value) {
            if ($predicateFunc($value, $key)) {
                $last = $value;
            }
        }

        if ($last === null) {
            throw new InvalidOperationException("No element satisfies the condition");
        }

        return $last;
    }

    // lastOrDefault

    /**
     * Sorts the elements of a sequence in ascending order according to a key
     * 
     * @param Closure $selector 
     * @param null|IComparer $comparer 
     * @param null|ITypeHint $TResult 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     * @throws InvalidOperationException 
     */
    public function orderBy(Closure $selector, ?IComparer $comparer = null): IEnumerable {
        

        
        throw new NotImplementedException("Not implemented");
        // $TResult ??= TypeHintFactory::undefined();

        // $selectorFunc = Func::new($selector, $this->T(), TypeHintFactory::keyTypes(), TypeHintFactory::keyTypes());

        // $comparer ??= Comparer::default($TResult);

        // $orderArray = $this->select($selectorFunc->getClosure())->toArray();

        

        // usort($orderArray, function($a, $b) use ($selectorFunc, $comparer) {
        //     return $comparer->compare($selectorFunc($a), $selectorFunc($b));
        // });

        // print_r($orderArray);

        // throw new InvalidOperationException("Not implemented");
        // return Enumerator::new($orderArray, $this->T());
    }

    // orderBy

    // orderByDescending

    /**
     * Projects each element of a sequence into a new form
     * 
     * @param Closure $selector 
     * @param null|Closure $keySelector 
     * @param null|ITypeHint $TResult 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public function select(Closure $selector, ?ITypeHint $TResult = null): IEnumerable {
        if ((string) ($TResult ??= TypeHintFactory::undefined()) === "void") {
            throw new InvalidArgumentException("TResult cannot be void");
        }

        $selectorFunc = Func::new($selector, $this->T(), TypeHintFactory::tryParse("null|int|string"), $TResult);
        
        return Enumerator::new((function() use ($selectorFunc): Generator {
            foreach ($this as $key => $value) {
                yield $selectorFunc($value, $key);
            }
        })(), $TResult);
    }

    // selectMany

    /**
     * Determines whether two sequences are equal by comparing the elements by using the default equality comparer for their type
     * 
     * @param Traversable $iterable 
     * @param null|IEqualityComparer $equalityComparer 
     * 
     * @return bool 
     * 
     * @throws InvalidArgumentException 
     */
    public function sequenceEqual(Traversable $iterable, ?IEqualityComparer $equalityComparer = null): bool {
        $equalityComparer ??= EqualityComparer::default($this->T());
        
        $iterableArray = array_values(iterator_to_array($iterable));
        $iterableArrayCount = count($iterableArray);

        $i = 0;

        foreach ($this as $key => $value) {
            if ($i >= $iterableArrayCount || !$equalityComparer->equals($value, $iterableArray[$i])) {
                return false;
            }

            $i ++;
        }

        return $i === $iterableArrayCount;
    }

    // single

    // singleOrDefault

    /**
     * Bypasses a specified number of elements in a sequence and then returns the remaining elements
     * 
     * @param int $count 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public function skip(int $count): IEnumerable {
        if ($count < 0) {
            throw new InvalidArgumentException("Count must be greater than or equal to zero");
        }

        return Enumerator::new((function() use ($count): Generator {
            $i = 0;

            foreach ($this as $key => $value) {
                if ($i >= $count) {
                    yield $key => $value;
                }

                $i ++;
            }
        })(), $this->T());
    }

    /**
     * Bypasses elements in a sequence as long as a specified condition is true and then returns the remaining elements
     * 
     * @param Closure $predicate 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public function skipWhile(Closure $predicate): IEnumerable {
        $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::keyTypes(), Type::bool());

        return Enumerator::new((function() use ($predicateFunc): Generator {
            $skipping = true;

            foreach ($this as $key => $value) {
                if ($skipping && !$predicateFunc($value, $key)) {
                    $skipping = false;
                }

                if (!$skipping) {
                    yield $key => $value;
                }
            }
        })(), $this->T());
    }

    /**
     * Computes the sum of the sequence of values
     * 
     * @param null|Closure $predicate 
     * 
     * @return mixed 
     */
    public function sum(?Closure $predicate = null) {
        return array_sum($this->select($predicate)->toArray());
    }

    /**
     * Swaps the keys and values of a sequence
     * 
     * @param null|Closure $predicate 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public function swapKeysAndValues(?Closure $predicate = null): IEnumerable {
        if ($predicate === null) {
            return Enumerator::new((function(): Generator {
                foreach ($this as $key => $value) {
                    if (!is_string($value) && !is_int($value)) {
                        throw new InvalidArgumentException("Value must be a string or integer");
                    }

                    yield $value => $key;
                }
            })(), TypeHintFactory::keyTypes());
        }

        $predicate = Func::new($predicate, $this->T(), TypeHintFactory::keyTypes(), TypeHintFactory::bool());

        return Enumerator::new((function() use ($predicate): Generator {
            foreach ($this as $key => $value) {
                if ($predicate($value, $key)) {
                    if (!is_string($value) && !is_int($value)) {
                        throw new InvalidArgumentException("Value must be a string or integer");
                    }

                    yield $value => $key;
                }
            }
        })(), TypeHintFactory::keyTypes());
    }

    /**
     * Returns a specified number of contiguous elements from the start of a sequence
     * 
     * @param int $count 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public function take(int $count): IEnumerable {
        if ($count < 0) {
            throw new InvalidArgumentException("Count must be greater than or equal to zero");
        }

        return Enumerator::new((function() use ($count): Generator {
            $i = 0;

            foreach ($this as $key => $value) {
                if ($i >= $count) {
                    break;
                }

                yield $key => $value;
                $i ++;
            }
        })(), $this->T());
    }

    /**
     * Returns elements from a sequence as long as a specified condition is true
     * 
     * @param Closure $predicate 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public function takeWhile(Closure $predicate): IEnumerable {
        $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::keyTypes(), Type::bool());

        return Enumerator::new((function() use ($predicateFunc): Generator {
            foreach ($this as $key => $value) {
                if (!$predicateFunc($value, $key)) {
                    break;
                }

                yield $key => $value;
            }
        })(), $this->T());
    }

    /**
     * Creates an array from a sequence
     * 
     * @return array 
     */
    public function toArray(?Closure $keySelector = null): array {
        if ($keySelector === null) {
            return iterator_to_array($this->getIterator());
        }

        $keySelector = Func::new($keySelector, $this->T(), TypeHintFactory::keyTypes(), TypeHintFactory::keyTypes(true));

        $array = [];

        foreach ($this as $key => $value) {
            $key = $keySelector($value, $key);

            if ($key === null) {
                $array[] = $value;
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * Creates a Collection from a sequence
     * 
     * @param null|Closure $keySelector 
     * 
     * @return ICollection 
     */
    public function toCollection(?Closure $keySelector = null): ICollection {
        return new Collection($this->toArray($keySelector), $this->T());
    }

    /**
     * Filters a sequence of values based on a predicate
     * 
     * @param Closure $predicate 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public function where(Closure $predicate): IEnumerable {
        $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::keyTypes(), Type::bool());

        return Enumerator::new((function() use ($predicateFunc): Generator {
            foreach ($this as $key => $value) {
                if ($predicateFunc($value, $key)) {
                    yield $key => $value;
                }
            }
        })(), $this->T());
    }
}