<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Enumerable;

use Pst\Core\Types\ITypeHint;
use Pst\Core\Interfaces\IEqualityComparer;
use Pst\Core\Collections\ICollection;
use Pst\Core\Collections\IReadonlyCollection;

use Closure;
use Traversable;

interface IImmutableEnumerableLinq {
    // aggregate
    public function all(Closure $predicate): bool;
    public function any(Closure $predicate): bool;
    public function append($element, $keyValue = null): IImmutableEnumerable;
    public function concat(iterable $iterable, ?Closure $iterableKeySelector = null): IImmutableEnumerable;
    public function count(?Closure $predicate = null): int;
    public function first(?Closure $predicate = null);
    public function firstOrDefault(?Closure $predicate = null);
    // firstOrDefault

    public function isEmpty(): bool;

    public function join(string $separator = ""): string;

    public function keys(?Closure $predicate = null): IImmutableEnumerable;

    public function last(?Closure $predicate = null);
    public function lastOrDefault(?Closure $predicate = null);
    // lastOrDefault
    // public function orderBy(Closure $selector, ?IComparer $comparer = null): IImmutableEnumerable;
    // orderByDescending
    public function select(Closure $selector, ?ITypeHint $TResult = null): IImmutableEnumerable;
    public function selectKey(Closure $keySelector): IImmutableEnumerable;
    public function selectValue(Closure $valueSelector): IImmutableEnumerable;
    public function selectMany(Closure $selector, ?ITypeHint $TResult = null): IImmutableEnumerable;
    // selectMany
    public function sequenceEqual(Traversable $iterable, ?IEqualityComparer $equalityComparer = null): bool;
    // single
    // singleOrDefault
    public function skip(int $count): IImmutableEnumerable;
    public function skipWhile(Closure $predicate): IImmutableEnumerable;

    public function sum(?Closure $selector = null);

    public function swapKeysAndValues(): IImmutableEnumerable;

    public function take(int $count): IImmutableEnumerable;
    public function takeWhile(Closure $predicate): IImmutableEnumerable;

    public function toArray(?Closure $keySelector = null): array;
    public function toCollection(?Closure $keySelector = null): ICollection;
    public function toReadonlyCollection(?Closure $keySelector = null): IReadonlyCollection;

    public function values(?Closure $predicate = null): IImmutableEnumerable;

    public function where(Closure $predicate): IImmutableEnumerable;
}