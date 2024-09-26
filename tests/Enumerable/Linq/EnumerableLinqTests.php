<?php

/*TDD*/

declare(strict_types=1);

require_once(__DIR__ . "/../../../vendor/autoload.php");

use Pst\Core\Enumerable\Enumerable;
use Pst\Core\Enumerable\IRewindableEnumerable;
use Pst\Core\Enumerable\RewindableEnumerable;
use Pst\Core\Exceptions\InvalidOperationException;

use Pst\Testing\Should;

Should::executeTests(function() {
    $mixedTestArray = [
        "ten" => 10,
        9 => "nine",
        "eight" => 8,
        7 => "seven",
        "six" => 6,
        5 => "five",
        "four" => 4,
        3 => "three",
        "two" => 2,
        1 => "one",
        "zero" => 0
    ];

    $mkMixedGenerator = fn() => (function() use ($mixedTestArray) {
        foreach ($mixedTestArray as $key => $value) {
            yield $key => $value;
        }
    })();

    $enumerable = RewindableEnumerable::create($mkMixedGenerator());

    Should::beTrue($enumerable->isRewindable());

    $enumerable = $enumerable->select(
        fn($value, $key) => !is_string($value) ? $key : $value,
        fn($value, $key) => is_string($value) ? $key : $value
    )->toRewindableEnumerable();

    // creation
    Should::beTrue(Enumerable::create([1,2,3,4,5,6,7,8,9,10])->sequenceEqual([1,2,3,4,5,6,7,8,9,10]));
    Should::beFalse(Enumerable::create([1,2,3,4,5,6,7,8,9,10])->sequenceEqual([1,2,3,4,5,6,7,8,9,10,11]));
    Should::beFalse(Enumerable::create([1,2,3,4,5,6,7,8,9,10,11])->sequenceEqual([1,2,3,4,5,6,7,8,9,10]));
    Should::beFalse(Enumerable::create([1,2,3,4,5,6,7,8,9,10])->sequenceEqual([10,9,8,7,6,5,4,3,2,1]));

    // sequenceEqual
    Should::beTrue($enumerable->sequenceEqual([10=>"ten", 9=>"nine", 8=>"eight", 7=>"seven", 6=>"six", 5=>"five", 4=>"four", 3=>"three", 2=>"two", 1=>"one", 0=>"zero"]));

    // all
    Should::beTrue($enumerable->all(fn($v,$k)=>is_string($v) && is_int($k)));
    Should::beFalse($enumerable->all(fn($v,$k)=> $v === "one"));

    // any
    Should::beTrue($enumerable->any(fn($v,$k)=> $v === "one"));
    Should::beFalse($enumerable->any(fn($v,$k)=> $v === "eleven"));

    // contains
    Should::beTrue($enumerable->contains("one"));
    Should::beFalse($enumerable->contains("eleven"));

    // containsKey
    Should::beTrue($enumerable->containsKey(1));
    Should::beFalse($enumerable->containsKey(11));

    // count
    Should::equal($enumerable->count(), 11);
    Should::equal($enumerable->count(fn($v,$k)=>$k > 5), 5);
    Should::equal($enumerable->count(fn($v,$k)=>$v === "one"), 1);

    // first
    Should::equal($enumerable->first(), "ten");
    Should::equal($enumerable->first(fn($v,$k)=>$k < 2), "one");
    Should::throw(InvalidOperationException::class, fn() => $enumerable->first(fn($v,$k)=>$v === "eleven"));

    // firstOrDefault
    Should::equal($enumerable->firstOrDefault(), "ten");
    Should::equal($enumerable->firstOrDefault(fn($v,$k)=>$v === "one"), "one");
    Should::equal($enumerable->firstOrDefault(fn($v,$k)=>$v === "eleven"), "");

    // firstKey
    Should::equal($enumerable->firstKey(), 10);
    Should::equal($enumerable->firstKey(fn($v,$k)=>$k < 2), 1);
    Should::throw(InvalidOperationException::class, fn() => $enumerable->firstKey(fn($v,$k)=>$k < 0));

    // firstKeyOrDefault
    Should::equal($enumerable->firstKeyOrDefault(), 10);
    Should::equal($enumerable->firstKeyOrDefault(fn($v,$k)=>$k < 2), 1);
    Should::equal($enumerable->firstKeyOrDefault(fn($v,$k)=>$k < 0), null);

    // iterationCount
    Should::equal($enumerable->iterationCount(fn($v,$k)=>$k < 5), 7);

    // keys
    Should::beTrue($enumerable->keys()->sequenceEqual([10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0]));
    Should::beTrue($enumerable->keys(fn($v,$k)=>$v === "one")->sequenceEqual([1]));

    // last
    Should::equal($enumerable->last(), "zero");
    Should::equal($enumerable->last(fn($v,$k)=>$k < 2), "zero");
    Should::throw(InvalidOperationException::class, fn() => $enumerable->last(fn($v,$k)=>$v === "eleven"));

    // lastOrDefault
    Should::equal($enumerable->lastOrDefault(), "zero");
    Should::equal($enumerable->lastOrDefault(fn($v,$k)=>$v === "one"), "one");
    Should::equal($enumerable->lastOrDefault(fn($v,$k)=>$v === "eleven"), "");

    // lastKey
    Should::equal($enumerable->lastKey(), 0);
    Should::equal($enumerable->lastKey(fn($v,$k)=>$k < 2), 0);
    Should::throw(InvalidOperationException::class, fn() => $enumerable->lastKey(fn($v,$k)=>$k < 0));

    // lastKeyOrDefault
    Should::equal($enumerable->lastKeyOrDefault(), 0);
    Should::equal($enumerable->lastKeyOrDefault(fn($v,$k)=>$k < 2), 0);
    Should::equal($enumerable->lastKeyOrDefault(fn($v,$k)=>$k < 0), null); // TODO, BUG, FIX: NOT SURE ABOUT THIS. NEED TO INVESTIGATE

    // single

    // singleOrDefault

    // skip
    Should::beTrue($enumerable->skip(5)->sequenceEqual(["five", "four", "three", "two", "one", "zero"]));

    // skipWhile
    Should::beTrue($enumerable->skipWhile(fn($v,$k)=>$k >= 6)->sequenceEqual(["five", "four", "three", "two", "one", "zero"]));

    // toArray
    Should::beTrue(is_array($enumerable->toArray()));
    Should::equal($enumerable->toArray(), [10=>"ten", 9=>"nine", 8=>"eight", 7=>"seven", 6=>"six", 5=>"five", 4=>"four", 3=>"three", 2=>"two", 1=>"one", 0=>"zero"]);

    // toCollection

    // toReadonlyCollection

    // toRewindableEnumerable
    Should::beA($enumerable->toRewindableEnumerable(), IRewindableEnumerable::class);

    // take
    Should::beTrue($enumerable->take(5)->sequenceEqual(["ten", "nine", "eight", "seven", "six"]));

    // takeWhile
    Should::beTrue($enumerable->takeWhile(fn($v,$k)=>$k > 5)->sequenceEqual(["ten", "nine", "eight", "seven", "six"]));

    // where

    // values
    Should::beTrue($enumerable->values()->sequenceEqual(["ten", "nine", "eight", "seven", "six", "five", "four", "three", "two", "one", "zero"]));
    Should::beFalse($enumerable->values()->sequenceEqual([10, "nine", 8, "seven", 6, "five", 4, "three", 2, "one", 0]));
});