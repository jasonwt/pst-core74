<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Enumerable\Linq;

use Pst\Core\Types\ITypeHint;
use Pst\Core\Enumerable\IEnumerable;
use Pst\Core\Enumerable\IRewindableEnumerable;
use Pst\Core\Collections\Collection;
use Pst\Core\Collections\ICollection;
use Pst\Core\Collections\IReadonlyCollection;
use Pst\Core\Collections\ReadonlyCollection;
use Pst\Core\Interfaces\IEqualityComparer;

use Closure;

trait EnumerableLinqTrait {
    public function all(Closure $predicate): bool {
        return Linq::all($this, $predicate);
    }

    public function any(Closure $predicate): bool {
        return Linq::any($this, $predicate);
    }

    public function contains($item): bool {
        return Linq::contains($this, $item);
    }

    public function containsKey($key): bool {
        return Linq::containsKey($this, $key);
    }

    public function count(?Closure $predicate = null): int {
        return Linq::count($this, $predicate);
    }

    public function first(?Closure $predicate = null, ?ITypeHint $T = null) {
        return Linq::first($this, $predicate, $T);
    }

    public function firstOrDefault(?Closure $predicate = null, ?ITypeHint $T = null) {
        return Linq::firstOrDefault($this, $predicate, $T);
    }

    public function iterationCount(Closure $predicate): int {
        return Linq::iterationCount($this, $predicate);
    }

    public function keys(?Closure $predicate = null): IEnumerable {
        return Linq::keys($this, $predicate);
    }

    public function last(?Closure $predicate = null, ?ITypeHint $T = null) {
        return Linq::last($this, $predicate, $T);
    }

    public function lastOrDefault(?Closure $predicate = null, ?ITypeHint $T = null) {
        return Linq::lastOrDefault($this, $predicate, $T);
    }

    public function select(?Closure $selector, ?Closure $keySelector = null, ?ITypeHint $T = null): IEnumerable {
        return Linq::select($this, $selector, $keySelector, $T);
    }

    public function sequenceEqual(iterable $other, ?IEqualityComparer $equalityComparer = null): bool {
        return Linq::sequenceEqual($this, $other, $equalityComparer);
    }

    public function single(?Closure $predicate = null, ?ITypeHint $T = null) {
        return Linq::single($this, $predicate, $T);
    }

    public function singleOrDefault(?Closure $predicate = null, ?ITypeHint $T = null) {
        return Linq::singleOrDefault($this, $predicate, $T);
    }

    public function skip(int $count): IEnumerable {
        return Linq::skip($this, $count);
    }

    public function skipWhile(Closure $predicate): IEnumerable {
        return Linq::skipWhile($this, $predicate);
    }

    public function toArray(): array {
        return Linq::toArray($this);
    }

    public function toCollection(): ICollection {
        return Collection::create($this);
    }

    public function toReadonlyCollection(): IReadonlyCollection {
        return ReadonlyCollection::create($this);
    }

    public function toRewindableEnumerable(): IRewindableEnumerable {
        return Linq::toRewindableEnumerable($this);
    }

    public function take(int $count): IEnumerable {
        return Linq::take($this, $count);
    }

    public function takeWhile(Closure $predicate): IEnumerable {
        return Linq::takeWhile($this, $predicate);
    }

    public function where(Closure $predicate): IEnumerable {
        return Linq::where($this, $predicate);
    }

    public function values(?Closure $predicate = null): IEnumerable {
        return Linq::values($this, $predicate);
    }
}