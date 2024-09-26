<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Enumerable\Linq;

use Pst\Core\Types\ITypeHint;
use Pst\Core\Enumerable\IEnumerable;

use Closure;
use Pst\Core\Collections\ICollection;
use Pst\Core\Collections\IReadonlyCollection;
use Pst\Core\Enumerable\IRewindableEnumerable;
use Pst\Core\Interfaces\IEqualityComparer;

interface IEnumerableLinq {
    public function all(Closure $predicate): bool;
    public function any(Closure $predicate): bool;
    public function count(?Closure $predicate = null): int;
    public function contains($value): bool;
    public function containsKey($key): bool;
    public function first(?Closure $predicate = null, ?ITypeHint $T = null);
    public function firstOrDefault(?Closure $predicate = null, ?ITypeHint $T = null);
    public function iterationCount(Closure $predicate): int;
    public function keys(?Closure $predicate = null): IEnumerable;
    public function last(?Closure $predicate = null, ?ITypeHint $T = null);
    public function lastOrDefault(?Closure $predicate = null, ?ITypeHint $T = null);
    public function select(?Closure $selector, ?Closure $keySelector = null, ?ITypeHint $T = null): IEnumerable;
    public function sequenceEqual(iterable $other, ?IEqualityComparer $equalityComparer = null): bool;
    public function single(Closure $predicate, ?ITypeHint $T = null); // not implemented
    public function singleOrDefault(?Closure $predicate = null, ?ITypeHint $T = null); // not implemented
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