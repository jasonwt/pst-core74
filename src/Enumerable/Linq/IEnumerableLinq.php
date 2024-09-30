<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Enumerable\Linq;

use Pst\Core\Types\ITypeHint;
use Pst\Core\Interfaces\IEqualityComparer;
use Pst\Core\Enumerable\IEnumerable;
use Pst\Core\Enumerable\IRewindableEnumerable;
use Pst\Core\Collections\ICollection;
use Pst\Core\Collections\IReadonlyCollection;

use Closure;

interface IEnumerableLinq {
    public function all(Closure $predicate): bool;
    public function any(Closure $predicate): bool;
    public function append(iterable $source, $element, $keyValue = null): IEnumerable;
    public function count(?Closure $predicate = null): int;
    public function contains($value): bool;
    public function containsKey($key): bool;
    public function first(?Closure $predicate = null);
    public function firstKey(?Closure $predicate = null);
    public function firstOrDefault(?Closure $predicate = null);
    public function firstKeyOrDefault(?Closure $predicate = null);
    public function groupBy(Closure $keySelector): IEnumerable;
    public function iterationCount(Closure $predicate): int;
    public function isEmpty(): bool;
    public function join(string $separator = ""): string;
    public function keys(?Closure $predicate = null): IEnumerable;
    public function keyMap(?Closure $keySelector = null, $TResult = null): IEnumerable;
    public function last(?Closure $predicate = null);
    public function lastKey(?Closure $predicate = null);
    public function lastOrDefault(?Closure $predicate = null);
    public function lastKeyOrDefault(?Closure $predicate = null);
    public function select(?Closure $selector, ?Closure $keySelector = null): IEnumerable;
    public function sequenceEqual(iterable $other, ?IEqualityComparer $equalityComparer = null): bool;
    public function single(Closure $predicate); // not implemented
    public function singleOrDefault(?Closure $predicate = null); // not implemented
    public function skip(int $count): IEnumerable;
    public function skipWhile(Closure $predicate): IEnumerable;
    public function toArray(): array;
    public function toCollection(): ICollection;
    public function toReadonlyCollection(): IReadonlyCollection;
    public function toRewindableEnumerable(): IRewindableEnumerable;
    public function take(int $count): IEnumerable;
    public function takeWhile(Closure $predicate): IEnumerable;
    public function where(Closure $predicate): IEnumerable;
    public function values(?Closure $predicate = null): IEnumerable;
}