<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Enumerable\Linq;

use Pst\Core\Func;
use Pst\Core\EqualityComparer;
use Pst\Core\Types\Type;
use Pst\Core\Types\ITypeHint;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Enumerable\Enumerable;
use Pst\Core\Enumerable\IEnumerable;
use Pst\Core\Enumerable\IRewindableEnumerable;
use Pst\Core\Enumerable\RewindableEnumerable;
use Pst\Core\Interfaces\IEqualityComparer;
use Pst\Core\Collections\Collection;
use Pst\Core\Collections\ICollection;
use Pst\Core\Collections\IReadonlyCollection;
use Pst\Core\Collections\ReadonlyCollection;

use Pst\Core\Exceptions\NotImplementedException;
use Pst\Core\Exceptions\InvalidOperationException;

use Closure;
use Generator;
use Traversable;

use InvalidArgumentException;

class Linq {
    // Pure static class
    private function __construct() {}

    /**
     * Determines whether all elements of a sequence satisfy a condition
     * 
     * @param iterable $source 
     * @param Closure $predicate 
     * 
     * @return bool 
     */
    public static function all(iterable $source, Closure $predicate): bool {
        $source = Enumerable::create($source);

        $predicateFunc = Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool());

        foreach ($source as $key => $value) {
            if (!$predicateFunc($value, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determines whether any element of a sequence satisfies a condition
     * 
     * @param iterable $source 
     * @param Closure $predicate 
     * 
     * @return bool 
     */
    public static function any(iterable $source, Closure $predicate): bool {
        $source = Enumerable::create($source);
        
        $predicateFunc = Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool());

        foreach ($source as $key => $value) {
            if ($predicateFunc($value, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Appends an element to the end of a sequence
     * 
     * @param iterable $source
     * @param mixed $element 
     * @param null|int|string $keyValue 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public static function append(iterable $source, $element, $keyValue = null): IEnumerable {
        $source = Enumerable::create($source);

        if (!Type::typeOf($element)->isAssignableTo($source->T())) {
            throw new InvalidArgumentException("element must be of type {$source->T()}");
        } else if (!Type::typeOf($keyValue)->isAssignableTo($source->TKey())) {
            throw new InvalidArgumentException("keyValue must be of type null, int or string");
        }

        return Enumerable::create((function() use ($source, $element, $keyValue): Generator {
            foreach ($source as $key => $value) {
                yield $key => $value;
            }

            if ($keyValue === null) {
                yield $element;
            } else {
                yield $keyValue => $element;
            }
        })(), $source->T());
    }

    /**
     * Determines whether a sequence contains a specified element
     * 
     * @param iterable $source 
     * @param mixed $value 
     * @param null|IEqualityComparer $equalityComparer 
     * 
     * @return bool 
     */
    public static function contains(iterable $source, $value, ?IEqualityComparer $equalityComparer = null): bool {
        $source = Enumerable::create($source);

        $equalityComparer ??= EqualityComparer::default($source->T());

        foreach ($source as $key => $element) {
            if ($equalityComparer->equals($element, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines whether a sequence contains a specified key
     * 
     * @param iterable $source 
     * @param mixed $key 
     * 
     * @return bool 
     */
    public static function containsKey(iterable $source, $key): bool {
        $source = Enumerable::create($source);

        foreach ($source as $sourceKey => $_) {
            if ($sourceKey === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the count of elements in a sequence that satisfy an optional predicate
     * 
     * @param iterable $source 
     * @param Closure|null $predicate
     * 
     * @return int
     */
    public static function count(iterable $source, ?Closure $predicate = null): int {
        $source = Enumerable::create($source);

        if ($predicate === null) {
            return iterator_count($source);
        }

        $predicateFunc = Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool());

        $count = 0;

        foreach ($source as $key => $value) {
            if ($predicateFunc($value, $key)) {
                $count ++;
            }
        }

        return $count;
    }

    /**
     * Returns distinct elements from a sequence
     * 
     * @param iterable $source 
     * @param null|Closure $predicate 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public static function first(iterable $source, ?Closure $predicate = null) {
        $source = Enumerable::create($source);

        $predicate = $predicate !== null ? 
            Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool()) :
            null;

        foreach ($source as $key => $value) {
            if ($predicate === null || $predicate($value, $key)) {
                return $value;
            }
        }

        throw new InvalidOperationException("Sequence contains no elements that satisfy the predicate");
    }

    /**
     * Returns the first key of a sequence matching the optional predicate
     * 
     * @param iterable $source 
     * @param null|Closure $predicate 
     * 
     * @return mixed 
     * 
     * @throws InvalidArgumentException 
     */
    public static function firstKey(iterable $source, ?Closure $predicate = null) {
        $source = Enumerable::create($source);

        $predicate = $predicate !== null ? 
            Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool()) :
            null;

        foreach ($source as $key => $value) {
            if ($predicate === null || $predicate($value, $key)) {
                return $key;
            }
        }

        throw new InvalidOperationException("Sequence contains no elements that satisfy the predicate");
    }

    /**
     * Returns the first element of a sequence, or a default value if the sequence contains no elements
     * 
     * @param iterable $source 
     * @param null|Closure $predicate 
     * 
     * @return mixed 
     * 
     * @throws InvalidArgumentException 
     */
    public static function firstOrDefault(iterable $source, ?Closure $predicate = null) {
        $source = Enumerable::create($source);

        $predicate = $predicate !== null ? 
            Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool()) :
            null;

        foreach ($source as $key => $value) {
            if ($predicate === null || $predicate($value, $key)) {
                return $value;
            }
        }

        return $source->T()->defaultValue();
    }

    /**
     * Returns the first key of a sequence matching the optional predicate, or a default value if the sequence contains no elements
     * 
     * @param iterable $source 
     * @param null|Closure $predicate 
     * 
     * @return mixed 
     * 
     * @throws InvalidArgumentException 
     */
    public static function firstKeyOrDefault(iterable $source, ?Closure $predicate = null) {
        $source = Enumerable::create($source);

        $predicate = $predicate !== null ? 
            Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool()) :
            null;

        foreach ($source as $key => $value) {
            if ($predicate === null || $predicate($value, $key)) {
                return $key;
            }
        }

        return $source->T()->defaultValue();
    }

    /**
     * Groups the elements of a sequence according to a specified key selector function
     * 
     * @param iterable $source 
     * @param Closure $keySelector 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public static function groupBy(iterable $source, Closure $keySelector, ?ITypeHint $T = null): IEnumerable {
        $source = Enumerable::create($source);

        $keySelectorFunc = Func::new($keySelector, $source->T(), Func::optionalParameter($source->TKey()), TypeHintFactory::keyTypes(true));

        $groups = [];

        foreach ($source as $key => $value) {
            $groupKey = $keySelectorFunc($value, $key);

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [];
            }

            $groups[$groupKey][] = $value;
        }

        foreach ($groups as $key => $group) {
            $groups[$key] = Enumerable::create($group, $source->T());
        }

        return Enumerable::create($groups, Type::interface(IEnumerable::class), $keySelectorFunc->getReturnTypeHint());
    }

    /**
     * Returns the number of elements in a sequence before a specified condition is true
     * 
     * @param iterable $source 
     * @param Closure $predicate 
     * 
     * @return int 
     * 
     * @throws InvalidArgumentException 
     */
    public static function iterationCount(iterable $source, Closure $predicate): int {
        $source = Enumerable::create($source);

        $predicateFunc = Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool());

        $count = 0;

        foreach ($source as $key => $value) {
            $count ++;

            if ($predicateFunc($value, $key)) {
                return $count;
            }
        }

        return -1;
    }

    /**
     * Determines whether a sequence is empty
     * 
     * @param iterable $source 
     * 
     * @return bool 
     */
    public static function isEmpty(iterable $source): bool {
        if (is_array($source)) {
            return count($source) === 0;
        } else if (!$source instanceof Traversable) {
            throw new InvalidArgumentException("Source must be an array or implement Traversable");
        }

        return iterator_count($source) === 0;
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
    public static function join(iterable $source, string $separator = ""): string {
        $stringValues = array_map(function($value) {
            if (is_string($value)) {
                return $value;
            } else if (is_int($value) || is_float($value)) {
                return (string) $value;
            } else if (is_scalar($value)) {
                return (string) $value;
            }

            throw new InvalidArgumentException("Value must be a string, integer, float or implement IToString");
        }, $source->toArray());
        
        return implode($separator, $stringValues);
    }

    /**
     * Returns the keys of a sequence
     * 
     * @param iterable $source 
     * @param null|Closure $predicate 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public static function keys(iterable $source, ?Closure $predicate = null): IEnumerable {
        $source = Enumerable::create($source);

        if ($predicate === null) {
            return Enumerable::create((function() use ($source): Generator {
                foreach ($source as $key => $_) {
                    yield $key;
                }
            })(), TypeHintFactory::keyTypes());
        }

        $predicateFunc = Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool());

        return Enumerable::create((function() use ($source, $predicateFunc): Generator {
            foreach ($source as $key => $value) {
                if ($predicateFunc($value, $key)) {
                    yield $key;
                }
            }
        })(), TypeHintFactory::keyTypes());
    }

    /**
     * Keeps the values of the sequence and sets the keys to the result of the keySelector function
     * 
     * @param iterable $source 
     * @param null|Closure $keySelector 
     * @param null|ITypeHint $TResult 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public static function keyMap(iterable $source, ?Closure $keySelector = null, ITypeHint $TResult = null): IEnumerable {
        $source = Enumerable::create($source);

        $TResult ??= TypeHintFactory::keyTypes(true);

        if ($keySelector === null) {
            return Enumerable::create(iterator_to_array($source, false), $source->T());
        }

        $keySelectorFunc = Func::new($keySelector, $source->T(), Func::optionalParameter($source->TKey()), TypeHintFactory::keyTypes(true));

        return Enumerable::create((function() use ($source, $keySelectorFunc): Generator {
            foreach ($source as $key => $value) {
                if (($key = $keySelectorFunc($value, $key)) !== null) {
                    yield $key => $value;
                } else {
                    yield $value;
                }

            }
        })(), $TResult);
    }


    /**
     * Returns the last element of a sequence
     * 
     * @param iterable $source 
     * @param null|Closure $predicate 
     * 
     * @return mixed 
     * 
     * @throws InvalidOperationException 
     * @throws InvalidArgumentException 
     */
    public static function last(iterable $source, ?Closure $predicate = null) {
        $source = Enumerable::create($source);

        $predicate = $predicate !== null ? 
            Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool()) :
            null;

        $last = null;
        $found = false;

        foreach ($source as $key => $value) {
            if ($predicate === null || $predicate($value, $key)) {
                $last = $value;
                $found = true;
            }
        }

        if (!$found) {
            throw new InvalidOperationException("Sequence contains no elements");
        }

        return $last;
    }

    /**
     * Returns the last key of a sequence
     * 
     * @param iterable $source 
     * @param null|Closure $predicate 
     * 
     * @return mixed 
     * 
     * @throws InvalidOperationException 
     * @throws InvalidArgumentException 
     */
    public static function lastKey(iterable $source, ?Closure $predicate = null) {
        $source = Enumerable::create($source);

        $predicate = $predicate !== null ? 
            Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool()) :
            null;

        $lastKey = null;
        $found = false;

        foreach ($source as $key => $value) {
            if ($predicate === null || $predicate($value, $key)) {
                $lastKey = $key;
                $found = true;
            }
        }

        if (!$found) {
            throw new InvalidOperationException("Sequence contains no elements");
        }

        return $lastKey;
    }

    /**
     * Returns the last element of a sequence, or a default value if the sequence contains no elements
     * 
     * @param iterable $source 
     * @param null|Closure $predicate 
     * 
     * @return mixed 
     * 
     * @throws InvalidArgumentException 
     */
    public static function lastOrDefault(iterable $source, ?Closure $predicate = null) {
        $source = Enumerable::create($source);

        $predicate = $predicate !== null ? 
            Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool()) :
            null;

        $last = null;
        $found = false;

        foreach ($source as $key => $value) {
            if ($predicate === null || $predicate($value, $key)) {
                $last = $value;
                $found = true;
            }
        }

        return $found ? $last : $source->T()->defaultValue();
    }

    /**
     * Returns the last key of a sequence, or a default value if the sequence contains no elements
     * 
     * @param iterable $source 
     * @param null|Closure $predicate 
     * 
     * @return mixed 
     * 
     * @throws InvalidArgumentException 
     */
    public static function lastKeyOrDefault(iterable $source, ?Closure $predicate = null) {
        $source = Enumerable::create($source);

        $predicate = $predicate !== null ? 
            Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool()) :
            null;

        $lastKey = null;
        $found = false;

        foreach ($source as $key => $value) {
            if ($predicate === null || $predicate($value, $key)) {
                $lastKey = $key;
                $found = true;
            }
        }

        return $found ? $lastKey : $source->T()->defaultValue();
    }

    /**
     * Projects each element of a sequence into a new form
     * 
     * @param iterable $source 
     * @param null|ITypeHint|Closure $selector 
     * @param null|ITypeHint $T 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public static function select(iterable $source, ?Closure $selector, ?Closure $keySelector = null): IEnumerable {
        $source = Enumerable::create($source, null);

        $selectorFunc = $selector === null ? null : 
            Func::new($selector, $source->T(), Func::optionalParameter($source->TKey()), null);

        $keySelectorFunc = $keySelector === null ? null : 
            Func::new($keySelector, $selectorFunc === null ? $source->T() : $selectorFunc->getReturnTypeHint(), Func::optionalParameter($source->TKey()), TypeHintFactory::keyTypes(true));

        if ($selectorFunc === null && $keySelectorFunc === null) {
            return $source;
        }

        if ($keySelectorFunc === null) {
            return Enumerable::create((function() use ($source, $selectorFunc): Generator {
                foreach ($source as $key => $value) {
                    yield $key => $selectorFunc($value, $key);
                }
            })(), $selectorFunc->getReturnTypeHint(), $source->TKey());
        }

        if ($selectorFunc === null) {
            return Enumerable::create((function() use ($source, $keySelectorFunc): Generator {
                foreach ($source as $key => $value) {
                    $yieldKey = $keySelectorFunc($value, $key);

                    if ($yieldKey === null) {
                        yield $value;
                    } else {
                        yield $yieldKey => $value;
                    }
                }
            })(), $source->T(), $keySelectorFunc->getReturnTypeHint());
        }

        return Enumerable::create((function() use ($source, $selectorFunc, $keySelectorFunc): Generator {
            foreach ($source as $key => $value) {
                $yieldKey = $keySelectorFunc($value, $key);

                if ($yieldKey === null) {
                    yield $selectorFunc($value, $key);
                } else {
                    yield $yieldKey => $selectorFunc($value, $key);
                }
            }
        })(), $selectorFunc->getReturnTypeHint(), $keySelectorFunc->getReturnTypeHint());
    }

    /**
     * Determines whether two sequences are equal by comparing the elements by using the default equality comparer for their type
     * 
     * @param iterable $source 
     * @param iterable $iterable 
     * @param null|IEqualityComparer $equalityComparer 
     * 
     * @return bool 
     * 
     * @throws InvalidArgumentException 
     */
    public static function sequenceEqual(iterable $source, iterable $iterable, ?IEqualityComparer $equalityComparer = null): bool {
        $source = Enumerable::create($source);
        $iterable = Enumerable::create($iterable);

        $sourceType = $source->T();
        $iterableType = $iterable->T();

        $equalityComparer ??= EqualityComparer::default($sourceType, $iterableType);

        foreach ($source as $key => $sourceValue) {
            if (!$iterable->getIterator()->valid() || $iterable->getIterator()->key() === null) {
                return false;
            }

            if (!$equalityComparer->equals($sourceValue, $iterable->getIterator()->current())) {
                return false;
            }

            $iterable->getIterator()->next();
        }

        return $iterable->getIterator()->key() === null;
    }

    /**
     * Returns a single element from a sequence that satisfies a specified condition
     * 
     * @param iterable $source 
     * @param Closure $predicate 
     * @param null|ITypeHint $T 
     * 
     * @return mixed 
     * 
     * @throws InvalidOperationException 
     * @throws InvalidArgumentException 
     */
    public static function single(iterable $source, Closure $predicate, ?ITypeHint $T = null) {
        $source = Enumerable::create($source);

        throw new NotImplementedException();
    }

    /**
     * Returns a single element from a sequence that satisfies a specified condition or a default value if no such element exists
     * 
     * @param iterable $source 
     * @param ?Closure $predicate 
     * @param null|ITypeHint $T 
     * 
     * @return mixed 
     * 
     * @throws InvalidOperationException 
     * @throws InvalidArgumentException 
     */
    public static function singleOrDefault(iterable $source, ?Closure $predicate = null, ?ITypeHint $T = null) {
        $source = Enumerable::create($source);

        throw new NotImplementedException();
    }

    /**
     * Bypasses a specified number of elements in a sequence and then returns the remaining elements
     * 
     * @param iterable $source 
     * @param int $count 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public static function skip(iterable $source, int $count): IEnumerable {
        $source = Enumerable::create($source);

        if ($count < 0) {
            throw new InvalidArgumentException("Count must be greater than or equal to zero");
        }

        return Enumerable::create((function() use ($source, $count): Generator {
            $i = 0;

            foreach ($source as $key => $value) {
                if ($i >= $count) {
                    yield $key => $value;
                }

                $i ++;
            }
        })(), $source->T());
    }

    /**
     * Bypasses elements in a sequence until a specified condition is false and then returns the remaining elements
     * 
     * @param iterable $source 
     * @param Closure $predicate 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public static function skipWhile(iterable $source, Closure $predicate) {
        $source = Enumerable::create($source);

        $predicateFunc = Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool());

        return Enumerable::create((function() use ($source, $predicateFunc): Generator {
            $skipping = true;

            foreach ($source as $key => $value) {
                if ($skipping && !$predicateFunc($value, $key)) {
                    $skipping = false;
                }

                if (!$skipping) {
                    yield $key => $value;
                }
            }
        })(), $source->T());
    }

    /**
     * Returns the first count element of a sequence
     * 
     * @param iterable $source 
     * @param null|Closure $predicate 
     * 
     * @return mixed 
     * 
     * @throws InvalidArgumentException 
     */
    public static function take(iterable $source, int $count): IEnumerable {
        $source = Enumerable::create($source);

        if ($count < 0) {
            throw new InvalidArgumentException("Count must be greater than or equal to zero");
        }

        return Enumerable::create((function() use ($source, $count): Generator {
            $i = 0;

            foreach ($source as $key => $value) {
                if ($i >= $count) {
                    break;
                }

                yield $key => $value;
                $i ++;
            }
        })(), $source->T());
    }

    /**
     * Returns elements from a sequence until a specified condition is false
     * 
     * @param iterable $source 
     * @param Closure $predicate 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public static function takeWhile(iterable $source, Closure $predicate): IEnumerable {
        $source = Enumerable::create($source);

        $predicateFunc = Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool());

        return Enumerable::create((function() use ($source, $predicateFunc): Generator {
            foreach ($source as $key => $value) {
                if (!$predicateFunc($value, $key)) {
                    break;
                }

                yield $key => $value;
            }
        })(), $source->T());
    }

    /**
     * Creates an array from a sequence
     * 
     * @param iterable $source 
     * 
     * @return array 
     */
    public static function toArray(iterable $source): array {
        return iterator_to_array(Enumerable::create($source));
    }

    /**
     * Creates a Collection from a sequence
     * 
     * @param iterable $source 
     * 
     * @return ICollection 
     */
    public static function toCollection(iterable $source): ICollection {
        return Collection::create($source);
    }

    /**
     * Creates a ReadonlyCollection from a sequence
     * 
     * @param iterable $source 
     * 
     * @return IReadonlyCollection 
     */
    public static function toReadonlyCollection(iterable $source): IReadonlyCollection {
        return ReadonlyCollection::create($source);
    }

    /**
     * Creates a RewindableEnumerable from a sequence
     * 
     * @param iterable $source 
     * 
     * @return IRewindableEnumerable 
     */
    public static function toRewindableEnumerable(iterable $source): IRewindableEnumerable {
        return RewindableEnumerable::create($source);
    }

    /**
     * Filters a sequence of values based on a predicate
     * 
     * @param iterable $source 
     * @param Closure $predicate 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public static function where(iterable $source, Closure $predicate): IEnumerable {
        $source = Enumerable::create($source);

        $predicateFunc = Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool());

        return Enumerable::create((function() use ($source, $predicateFunc): Generator {
            foreach ($source as $key => $value) {
                if ($predicateFunc($value, $key)) {
                    yield $key => $value;
                }
            }
        })(), $source->T());
    }
    
    /**
     * Filters a sequence of values based on a predicate
     * 
     * @param iterable $source 
     * @param null|Closure $predicate 
     * 
     * @return IEnumerable 
     * 
     * @throws InvalidArgumentException 
     */
    public static function values(iterable $source, ?Closure $predicate = null): IEnumerable {
        $source = Enumerable::create($source);

        if ($predicate === null) {
            return Enumerable::create((function() use ($source): Generator {
                foreach ($source as $key => $value) {
                    yield $value;
                }
            })(), $source->T());
        }

        $predicateFunc = Func::new($predicate, $source->T(), Func::optionalParameter($source->TKey()), Type::bool());

        return Enumerable::create((function() use ($source, $predicateFunc): Generator {
            foreach ($source as $key => $value) {
                if ($predicateFunc($value, $key)) {
                    yield $value;
                }
            }
        })(), $source->T());
    }
}



// <?php

// declare(strict_types=1);

// namespace Pst\Core\Enumerable;


// use Pst\Core\Func;
// use Pst\Core\Comparer;
// use Pst\Core\EqualityComparer;
// use Pst\Core\Types\Type;
// use Pst\Core\Types\ITypeHint;
// use Pst\Core\Types\TypeHintFactory;
// use Pst\Core\Interfaces\IComparer;
// use Pst\Core\Interfaces\IToString;
// use Pst\Core\Interfaces\IEqualityComparer;
// use Pst\Core\Collections\Collection;
// use Pst\Core\Collections\ICollection;
// use Pst\Core\Collections\ReadonlyCollection;
// use Pst\Core\Collections\IReadonlyCollection;

// use Pst\Core\Exceptions\InvalidOperationException;

// use Closure;
// use Generator;
// use Traversable;
// use InvalidArgumentException;

// trait ImmutableEnumerableLinqTrait {
//     public abstract function T(): ITypeHint;

//     // aggregate





//     /**
//      * Concatenates two sequences
//      * 
//      * @param iterable $iterable 
//      * 
//      * @return IImmutableEnumerable 
//      * 
//      * @throws InvalidArgumentException 
//      */
//     public function concat(iterable $iterable, ?Closure $iterableKeySelector = null): IImmutableEnumerable {
//         $iterable = Enumerable::create($iterable, $source->T());

//         return Enumerable::create((function() use ($iterable, $iterableKeySelector): Generator {
//             foreach ($source as $key => $value) {
//                 yield $key => $value;
//             }

//             if ($iterableKeySelector === null) {
//                 foreach ($iterable as $key => $value) {
//                     yield $key => $value;
//                 }
//             } else {
//                 $iterableKeySelector = Func::new($iterableKeySelector, $iterable->T(), Func::optionalParameter($source->TKey()), TypeHintFactory::keyTypes(true));

//                 foreach ($iterable as $key => $value) {
//                     $key = $iterableKeySelector($value, $key);

//                     if ($key === null) {
//                         yield $value;
//                     } else {
//                         yield $key => $value;
//                     }
//                 }
//             }
//         })(), $source->T());
//     }




//     /**
//      * Determines whether a sequence is empty
//      * 
//      * @return bool
//      */
//     public function isEmpty(): bool {
//         return $source->any(            
//             function($value, $key) { return false; }, 
//         );
//     }

//     

//     /**
//      * Concatenates the members of a collection, using the specified separator between each member
//      * 
//      * @param string $separator 
//      * 
//      * @return string 
//      * 
//      * @throws InvalidArgumentException
//      */
//     public function join(string $separator = ""): string {
//         $stringValues = array_map(function($value) {
//             if (is_string($value)) {
//                 return $value;
//             } else if (is_int($value) || is_float($value)) {
//                 return (string) $value;
//             } else if (!$value instanceof IToString) {
//                 throw new InvalidArgumentException("Value must be a string, integer, float or implement IToString");
//             }

//             return $value->toString();
//         }, $source->toArray());
        
//         return implode($separator, $stringValues);
//     }


//     // orderBy

//     // orderByDescending






//     public function selectMany(Closure $selector, ?ITypeHint $TResult = null): IImmutableEnumerable {
//         throw new NotImplementedException("Not implemented");
//         // if ((string) ($TResult ??= TypeHintFactory::undefined()) === "void") {
//         //     throw new InvalidArgumentException("TResult cannot be void");
//         // }

//         // $selectorFunc = Func::new($selector, $source->T(), Func::optionalParameter($source->TKey()), $TResult);

//         // return Enumerable::create((function() use ($selectorFunc): Generator {
//         //     foreach ($source as $key => $value) {
//         //         $enumerable = $selectorFunc($value, $key);

//         //         foreach ($enumerable as $innerKey => $innerValue) {
//         //             yield $innerKey => $innerValue;
//         //         }
//         //     }
//         // })(), $TResult);
//     }

//     // selectMany

//     /**
//      * Determines whether two sequences are equal by comparing the elements by using the default equality comparer for their type
//      * 
//      * @param Traversable $iterable 
//      * @param null|IEqualityComparer $equalityComparer 
//      * 
//      * @return bool 
//      * 
//      * @throws InvalidArgumentException 
//      */
//     public function sequenceEqual(Traversable $iterable, ?IEqualityComparer $equalityComparer = null): bool {
//         $equalityComparer ??= EqualityComparer::default($source->T());
        
//         $iterableArray = array_values(iterator_to_array($iterable));
//         $iterableArrayCount = count($iterableArray);

//         $i = 0;

//         foreach ($source as $key => $value) {
//             if ($i >= $iterableArrayCount || !$equalityComparer->equals($value, $iterableArray[$i])) {
//                 return false;
//             }

//             $i ++;
//         }

//         return $i === $iterableArrayCount;
//     }

//     // single

//     // singleOrDefault



//     /**
//      * Computes the sum of the sequence of values
//      * 
//      * @param null|Closure $predicate 
//      * 
//      * @return mixed 
//      */
//     public function sum(?Closure $predicate = null) {
//         return array_sum($source->select($predicate)->toArray());
//     }




//     /**
//      * Swaps the keys and values of a sequence
//      * 
//      * @param null|Closure $predicate 
//      * 
//      * @return IImmutableEnumerable 
//      * 
//      * @throws InvalidArgumentException 
//      */
//     public function swapKeysAndValues(?Closure $predicate = null): IImmutableEnumerable {
//         if ($predicate === null) {
//             return Enumerable::create((function(): Generator {
//                 foreach ($source as $key => $value) {
//                     if (!is_string($value) && !is_int($value)) {
//                         throw new InvalidArgumentException("Value must be a string or integer");
//                     }

//                     yield $value => $key;
//                 }
//             })(), TypeHintFactory::keyTypes());
//         }

//         $predicate = Func::new($predicate, $source->T(), TypeHintFactory::keyTypes(), TypeHintFactory::bool());

//         return Enumerable::create((function() use ($predicate): Generator {
//             foreach ($source as $key => $value) {
//                 if ($predicate($value, $key)) {
//                     if (!is_string($value) && !is_int($value)) {
//                         throw new InvalidArgumentException("Value must be a string or integer");
//                     }

//                     yield $value => $key;
//                 }
//             }
//         })(), TypeHintFactory::keyTypes());
//     }









//     /**
//      * Creates an array from a sequence
//      * 
//      * BUG: TODO: FIX: key is always return 0
//      * 
//      * @return array 
//      */
//     public function toArray(?Closure $keySelector = null): array {
//         $array = [];

//         if ($keySelector === null) {
//             foreach ($source as $key => $value) {
//                 if ($key === null) {
//                     $array[] = $value;
//                 } else {
//                     $array[$key] = $value;
//                 }
//             }

//             return $array;
//         }

//         $keySelector = Func::new($keySelector, $source->T(), Func::optionalParameter($source->TKey()), TypeHintFactory::keyTypes(true));

//         foreach ($source as $key => $value) {
//             $key = $keySelector($value, $key);

//             if ($key === null) {
//                 $array[] = $value;
//             } else {
//                 $array[$key] = $value;
//             }
//         }

//         return $array;
//     }

//     /**
//      * Creates a Collection from a sequence
//      * 
//      * @param null|Closure $keySelector 
//      * 
//      * @return ICollection 
//      */
//     public function toCollection(?Closure $keySelector = null): ICollection {
//         return Collection::create($source->toArray($keySelector), $source->T());
//     }

//     /**
//      * Creates a ReadonlyCollection from a sequence
//      * 
//      * @param null|Closure $keySelector 
//      * 
//      * @return IReadonlyCollection 
//      */
//     public function toReadonlyCollection(?Closure $keySelector = null): IReadonlyCollection {
//         return ReadonlyCollection::create($source->toArray($keySelector), $source->T());
//     }



// }