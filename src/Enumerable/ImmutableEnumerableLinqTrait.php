<?php

declare(strict_types=1);

namespace Pst\Core\Enumerable;


use Pst\Core\Func;
use Pst\Core\Comparer;
use Pst\Core\EqualityComparer;
use Pst\Core\Types\Type;
use Pst\Core\Types\ITypeHint;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Interfaces\IComparer;
use Pst\Core\Interfaces\IToString;
use Pst\Core\Interfaces\IEqualityComparer;
use Pst\Core\Collections\Collection;
use Pst\Core\Collections\ICollection;
use Pst\Core\Collections\ReadonlyCollection;
use Pst\Core\Collections\IReadonlyCollection;

use Pst\Core\Exceptions\NotImplementedException;
use Pst\Core\Exceptions\InvalidOperationException;

use Closure;
use Generator;
use Traversable;
use InvalidArgumentException;

trait ImmutableEnumerableLinqTrait {
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
        $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::tryParse("int|string|void"), Type::bool());
        
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
        $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::tryParse("int|string|void"), Type::bool());

        foreach ($this as $key => $value) {
            if ($predicateFunc($value, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Appends an element to the end of a sequence
     * 
     * @param mixed $element 
     * @param null|int|string $keyValue 
     * 
     * @return IImmutableEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public function append($element, $keyValue = null): IImmutableEnumerable {
        if (!Type::typeOf($element)->isAssignableTo($this->T())) {
            throw new InvalidArgumentException("element must be of type {$this->T()}");
        } else if (!Type::typeOf($keyValue)->isAssignableTo(TypeHintFactory::keyTypes(true))) {
            throw new InvalidArgumentException("keyValue must be of type null, int or string");
        }

        return Enumerator::new((function() use ($element, $keyValue): Generator {
            foreach ($this as $key => $value) {
                yield $key => $value;
            }

            if ($keyValue === null) {
                yield $element;
            } else {
                yield $keyValue => $element;
            }
        })(), $this->T());
    }

    /**
     * Concatenates two sequences
     * 
     * @param iterable $iterable 
     * 
     * @return IImmutableEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public function concat(iterable $iterable, ?Closure $iterableKeySelector = null): IImmutableEnumerable {
        $iterable = Enumerator::new($iterable, $this->T());

        return Enumerator::new((function() use ($iterable, $iterableKeySelector): Generator {
            foreach ($this as $key => $value) {
                yield $key => $value;
            }

            if ($iterableKeySelector === null) {
                foreach ($iterable as $key => $value) {
                    yield $key => $value;
                }
            } else {
                $iterableKeySelector = Func::new($iterableKeySelector, $iterable->T(), TypeHintFactory::tryParse("int|string|void"), TypeHintFactory::keyTypes(true));

                foreach ($iterable as $key => $value) {
                    $key = $iterableKeySelector($value, $key);

                    if ($key === null) {
                        yield $value;
                    } else {
                        yield $key => $value;
                    }
                }
            }
        })(), $this->T());
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
    public function linqCount(?Closure $predicate = null): int {
        if ($predicate === null) {
            return iterator_count($this);
        }

        $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::tryParse("int|string|void"), Type::bool());
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

        $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::tryParse("int|string|void"), Type::bool());

        foreach ($this as $key => $value) {
            if ($predicateFunc($value, $key)) {
                return $value;
            }
        }

        throw new InvalidOperationException("No element satisfies the condition");
    }

    /**
     * Returns the first element of a sequence, or a default value if the sequence contains no elements
     * 
     * @param null|Closure $predicate 
     * 
     * @return mixed 
     * 
     * @throws InvalidArgumentException 
     */
    public function firstOrDefault(?Closure $predicate = null) {
        if ($predicate === null) {
            foreach ($this as $value) {
                return $value;
            }
        } else {
            $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::tryParse("int|string|void"), Type::bool());

            foreach ($this as $key => $value) {
                if ($predicateFunc($value, $key)) {
                    return $value;
                }
            }
        }

        if (($typeGetTypeInfo = Type::getTypeInfo((string) $this->T())) === null) {
            return Type::tryParse($typeGetTypeInfo["name"])->defaultValue();
        }

        return null;
    }

    /**
     * Determines whether a sequence is empty
     * 
     * @return bool
     */
    public function isEmpty(): bool {
        return $this->any(            
            function($value, $key) { return false; }, 
        );
    }

    /**
     * Returns the keys of a sequence as an IImmutableEnumerable
     * 
     * @param null|Closure $predicate
     * 
     * @return IImmutableEnumerable
     */
    public function linqKeys(?Closure $predicate = null): IImmutableEnumerable {
        if ($predicate === null) {
            return Enumerator::new((function(): Generator {
                foreach ($this as $key => $value) {
                    yield $key;
                }
            })(), TypeHintFactory::keyTypes());
        }

        $predicate = Func::new($predicate, $this->T(), TypeHintFactory::tryParse("int|string|void"), Type::bool());

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
        $foundItem = false;
        $last = null;

        if ($predicate === null) {
            foreach ($this as $value) {
                $foundItem = true;
                $last = $value;
            }
        } else {
            $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::tryParse("int|string|void"), Type::bool());
    
            foreach ($this as $key => $value) {
                if ($predicateFunc($value, $key)) {
                    $foundItem = true;
                    $last = $value;
                }
            }
        }

        if ($foundItem) {
            return $last;
        }

        throw new InvalidOperationException("No element satisfies the condition");
    }

    /**
     * Returns the last element of a sequence, or a default value if the sequence contains no elements
     * 
     * @param null|Closure $predicate 
     * 
     * @return mixed 
     * 
     * @throws InvalidArgumentException 
     */
    public function lastOrDefault(?Closure $predicate = null) {
        $foundItem = false;
        $last = null;

        if ($predicate === null) {
            foreach ($this as $value) {
                $foundItem = true;
                $last = $value;
            }
        } else {
            $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::tryParse("int|string|void"), Type::bool());
    
            foreach ($this as $key => $value) {
                if ($predicateFunc($value, $key)) {
                    $foundItem = true;
                    $last = $value;
                }
            }
        }

        if ($foundItem) {
            return $last;
        }

        if (($typeGetTypeInfo = Type::getTypeInfo((string) $this->T())) === null) {
            return Type::tryParse($typeGetTypeInfo["name"])->defaultValue();
        }

        return null;
    }

    // lastOrDefault

    // orderBy

    // orderByDescending

    /**
     * Projects each element of a sequence into a new form
     * 
     * @param Closure $selector 
     * @param null|Closure $keySelector 
     * @param null|ITypeHint $TResult 
     * 
     * @return IImmutableEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public function select(Closure $selector, ?ITypeHint $TResult = null): IImmutableEnumerable {
        if ((string) ($TResult ??= TypeHintFactory::undefined()) === "void") {
            throw new InvalidArgumentException("TResult cannot be void");
        }

        $selectorFunc = Func::new($selector, $this->T(), TypeHintFactory::tryParse("int|string|void"), $TResult);
        
        return Enumerator::new((function() use ($selectorFunc): Generator {
            foreach ($this as $key => $value) {
                //yield $selectorFunc($value, $key);
                yield $key => $selectorFunc($value, $key);
            }
        })(), $TResult);
    }


    /**
     * Specifies a key selector function to select the key for each element
     * 
     * @param Closure $keySelector 
     * 
     * @return IImmutableEnumerable 
     * @throws InvalidArgumentException 
     * @throws NotImplementedException 
     */    
    public function selectKey(Closure $keySelector): IImmutableEnumerable {
        $keySelector = Func::new($keySelector, $this->T(), TypeHintFactory::tryParse("int|string|void"), TypeHintFactory::keyTypes(true));

        return Enumerator::new((function() use ($keySelector): Generator {
            foreach ($this as $key => $value) {
                yield $keySelector($value, $key) => $value;
            }
        })(), $this->T());
    }

    public function selectValue(Closure $valueSelector): IImmutableEnumerable {
        $valueSelector = Func::new($valueSelector, $this->T(), TypeHintFactory::tryParse("int|string|void"), $this->T());

        return Enumerator::new((function() use ($valueSelector): Generator {
            foreach ($this as $key => $value) {
                yield $key => $valueSelector($value, $key);
            }
        })(), $this->T());
    }

    public function selectMany(Closure $selector, ?ITypeHint $TResult = null): IImmutableEnumerable {
        throw new NotImplementedException("Not implemented");
        // if ((string) ($TResult ??= TypeHintFactory::undefined()) === "void") {
        //     throw new InvalidArgumentException("TResult cannot be void");
        // }

        // $selectorFunc = Func::new($selector, $this->T(), TypeHintFactory::tryParse("int|string|void"), $TResult);

        // return Enumerator::new((function() use ($selectorFunc): Generator {
        //     foreach ($this as $key => $value) {
        //         $enumerable = $selectorFunc($value, $key);

        //         foreach ($enumerable as $innerKey => $innerValue) {
        //             yield $innerKey => $innerValue;
        //         }
        //     }
        // })(), $TResult);
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
     * @return IImmutableEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public function skip(int $count): IImmutableEnumerable {
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
     * @return IImmutableEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public function skipWhile(Closure $predicate): IImmutableEnumerable {
        $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::tryParse("int|string|void"), Type::bool());

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
     * @return IImmutableEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public function swapKeysAndValues(?Closure $predicate = null): IImmutableEnumerable {
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
     * @return IImmutableEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public function take(int $count): IImmutableEnumerable {
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
     * @return IImmutableEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public function takeWhile(Closure $predicate): IImmutableEnumerable {
        $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::tryParse("int|string|void"), Type::bool());

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
     * BUG: TODO: FIX: key is always return 0
     * 
     * @return array 
     */
    public function toArray(?Closure $keySelector = null): array {
        $array = [];

        if ($keySelector === null) {
            foreach ($this as $key => $value) {
                if ($key === null) {
                    $array[] = $value;
                } else {
                    $array[$key] = $value;
                }
            }

            return $array;
        }

        $keySelector = Func::new($keySelector, $this->T(), TypeHintFactory::tryParse("int|string|void"), TypeHintFactory::keyTypes(true));

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
        return Collection::new($this->toArray($keySelector), $this->T());
    }

    /**
     * Creates a ReadonlyCollection from a sequence
     * 
     * @param null|Closure $keySelector 
     * 
     * @return IReadonlyCollection 
     */
    public function toReadonlyCollection(?Closure $keySelector = null): IReadonlyCollection {
        return ReadonlyCollection::new($this->toArray($keySelector), $this->T());
    }

    /**
     * Returns the values of a sequence as an IImmutableEnumerable
     * 
     * @param null|Closure $predicate 
     * 
     * @return IImmutableEnumerable 
     */
    public function linqValues(?Closure $predicate = null): IImmutableEnumerable {
        if ($predicate === null) {
            return Enumerator::new((function(): Generator {
                foreach ($this as $key => $value) {
                    yield $value;
                }
            })(), $this->T());
        }

        $predicate = Func::new($predicate, $this->T(), TypeHintFactory::tryParse("int|string|void"), Type::bool());

        return Enumerator::new((function() use ($predicate): Generator {
            foreach ($this as $key => $value) {
                if ($predicate($value, $key)) {
                    yield $value;
                }
            }
        })(), $this->T());
    }

    /**
     * Filters a sequence of values based on a predicate
     * 
     * @param Closure $predicate 
     * 
     * @return IImmutableEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public function where(Closure $predicate): IImmutableEnumerable {
        $predicateFunc = Func::new($predicate, $this->T(), TypeHintFactory::tryParse("int|string|void"), Type::bool());

        return Enumerator::new((function() use ($predicateFunc): Generator {
            foreach ($this as $key => $value) {
                if ($predicateFunc($value, $key)) {
                    yield $key => $value;
                }
            }
        })(), $this->T());
    }
}