<?php

declare(strict_types=1);

namespace Pst\Core\Collections;

use Pst\Core\IComparer;
use Pst\Core\IToArray;
use Pst\Core\IEqualityComparer;
use Pst\Core\Types\ITypeHint;

use Closure;
use Countable;
use Traversable;
use IteratorAggregate;

interface IEnumerable extends Countable, IteratorAggregate, IToArray {
    public function T(): ITypeHint;

    // aggregate
    public function all(Closure $predicate): bool;
    public function any(Closure $predicate): bool;
    public function count(?Closure $predicate = null): int;
    public function first(?Closure $predicate = null);
    // firstOrDefault

    public function join(string $separator = ""): string;

    public function keys(?Closure $predicate = null): IEnumerable;

    public function last(?Closure $predicate = null);
    // lastOrDefault
    //public function orderBy(Closure $selector, ?IComparer $comparer = null): IEnumerable;
    // orderByDescending
    public function select(Closure $selector, ?ITypeHint $TResult = null): IEnumerable;
    public function selectMany(Closure $selector, ?ITypeHint $TResult = null): IEnumerable;
    // selectMany
    public function sequenceEqual(Traversable $iterable, ?IEqualityComparer $equalityComparer = null): bool;
    // single
    // singleOrDefault
    public function skip(int $count): IEnumerable;
    public function skipWhile(Closure $predicate): IEnumerable;
    public function sum(?Closure $selector = null);
    public function swapKeysAndValues(): IEnumerable;
    public function take(int $count): IEnumerable;
    public function takeWhile(Closure $predicate): IEnumerable;
    public function toArray(?Closure $keySelector = null): array;
    public function toCollection(?Closure $keySelector = null): ICollection;

    public function where(Closure $predicate): IEnumerable;
}