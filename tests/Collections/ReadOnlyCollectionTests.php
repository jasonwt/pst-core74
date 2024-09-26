<?php

/*TDD*/

declare(strict_types=1);

require_once(__DIR__ . "/../../vendor/autoload.php");

use Pst\Core\Collections\Collection;
use Pst\Core\Collections\ReadonlyCollection;
use Pst\Core\Enumerable\Enumerable;
use Pst\Core\Enumerable\IRewindableEnumerable;

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

    // creation
    Should::beTrue(ReadonlyCollection::create([1,2,3,4,5,6,7,8,9,10])->sequenceEqual([1,2,3,4,5,6,7,8,9,10]));
    Should::beFalse(ReadonlyCollection::create([1,2,3,4,5,6,7,8,9,10])->sequenceEqual([1,2,3,4,5,6,7,8,9,10,11]));
    Should::beFalse(ReadonlyCollection::create([1,2,3,4,5,6,7,8,9,10,11])->sequenceEqual([1,2,3,4,5,6,7,8,9,10]));
    Should::beFalse(ReadonlyCollection::create([1,2,3,4,5,6,7,8,9,10])->sequenceEqual([10,9,8,7,6,5,4,3,2,1]));

    $enumerable = Enumerable::create($mkMixedGenerator());

    // select
    $collection = $enumerable->select(
        fn($value, $key) => !is_string($value) ? $key : $value,
        fn($value, $key) => is_string($value) ? $key : $value
    )->toCollection();

    // isRewindable
    Should::beTrue($collection->isRewindable() && $collection instanceof IRewindableEnumerable);

    //print_r($collection);

    // sequenceEqual
    Should::beTrue($collection->sequenceEqual([10=>"ten", 9=>"nine", 8=>"eight", 7=>"seven", 6=>"six", 5=>"five", 4=>"four", 3=>"three", 2=>"two", 1=>"one", 0=>"zero"]));

    // all
    Should::beTrue($collection->all(fn($v,$k)=>is_string($v) && is_int($k)));
    Should::beFalse($collection->all(fn($v,$k)=> $v === "one"));

    // any
    Should::beTrue($collection->any(fn($v,$k)=> $v === "one"));
    Should::beFalse($collection->any(fn($v,$k)=> $v === "eleven"));

    // contains
    Should::beTrue($collection->contains("one"));
    Should::beFalse($collection->contains("eleven"));

    // containsKey
    Should::beTrue($collection->containsKey(1));
    Should::beFalse($collection->containsKey(11));

    // count
    Should::equal($collection->count(), 11);
    Should::equal($collection->count(fn($v,$k)=>$k > 5), 5);
    Should::equal($collection->count(fn($v,$k)=>$v === "one"), 1);

    // first
    Should::equal($collection->first(), "ten");
    Should::equal($collection->first(fn($v,$k)=>$k < 2), "one");
    Should::throw(InvalidOperationException::class, fn() => $collection->first(fn($v,$k)=>$v === "eleven"));

    // firstOrDefault
    Should::equal($collection->firstOrDefault(), "ten");
    Should::equal($collection->firstOrDefault(fn($v,$k)=>$v === "one"), "one");
    Should::equal($collection->firstOrDefault(fn($v,$k)=>$v === "eleven"), "");

    // firstKey
    Should::equal($collection->firstKey(), 10);
    Should::equal($collection->firstKey(fn($v,$k)=>$k < 2), 1);
    Should::throw(InvalidOperationException::class, fn() => $collection->firstKey(fn($v,$k)=>$k < 0));

    // firstKeyOrDefault
    Should::equal($collection->firstKeyOrDefault(), 10);
    Should::equal($collection->firstKeyOrDefault(fn($v,$k)=>$k < 2), 1);
    Should::equal($collection->firstKeyOrDefault(fn($v,$k)=>$k < 0), null);

    // iterationCount
    Should::equal($collection->iterationCount(fn($v,$k)=>$k < 5), 7);

    // keys
    Should::beTrue($collection->keys()->sequenceEqual([10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0]));
    Should::beTrue($collection->keys(fn($v,$k)=>$v === "one")->sequenceEqual([1]));

    // last
    Should::equal($collection->last(), "zero");
    Should::equal($collection->last(fn($v,$k)=>$k < 2), "zero");
    Should::throw(InvalidOperationException::class, fn() => $collection->last(fn($v,$k)=>$v === "eleven"));

    // lastOrDefault
    Should::equal($collection->lastOrDefault(), "zero");
    Should::equal($collection->lastOrDefault(fn($v,$k)=>$v === "one"), "one");
    Should::equal($collection->lastOrDefault(fn($v,$k)=>$v === "eleven"), "");

    // lastKey
    Should::equal($collection->lastKey(), 0);
    Should::equal($collection->lastKey(fn($v,$k)=>$k < 2), 0);
    Should::throw(InvalidOperationException::class, fn() => $collection->lastKey(fn($v,$k)=>$k < 0));

    // lastKeyOrDefault
    Should::equal($collection->lastKeyOrDefault(), 0);
    Should::equal($collection->lastKeyOrDefault(fn($v,$k)=>$k < 2), 0);
    Should::equal($collection->lastKeyOrDefault(fn($v,$k)=>$k < 0), null); // TODO, BUG, FIX: NOT SURE ABOUT THIS. NEED TO INVESTIGATE

    // single

    // singleOrDefault

    // skip
    Should::beTrue($collection->skip(5)->sequenceEqual(["five", "four", "three", "two", "one", "zero"]));

    // skipWhile
    Should::beTrue($collection->skipWhile(fn($v,$k)=>$k >= 6)->sequenceEqual(["five", "four", "three", "two", "one", "zero"]));

    // toArray
    Should::beTrue(is_array($collection->toArray()));
    Should::equal($collection->toArray(), [10=>"ten", 9=>"nine", 8=>"eight", 7=>"seven", 6=>"six", 5=>"five", 4=>"four", 3=>"three", 2=>"two", 1=>"one", 0=>"zero"]);

    // toCollection

    // toReadonlyCollection

    // toRewindableEnumerable
    Should::beA($collection->toRewindableEnumerable(), IRewindableEnumerable::class);

    // take
    Should::beTrue($collection->take(5)->sequenceEqual(["ten", "nine", "eight", "seven", "six"]));

    // takeWhile
    Should::beTrue($collection->takeWhile(fn($v,$k)=>$k > 5)->sequenceEqual(["ten", "nine", "eight", "seven", "six"]));

    // where

    // values
    Should::beTrue($collection->values()->sequenceEqual(["ten", "nine", "eight", "seven", "six", "five", "four", "three", "two", "one", "zero"]));
    Should::beFalse($collection->values()->sequenceEqual([10, "nine", 8, "seven", 6, "five", 4, "three", 2, "one", 0])); 
});