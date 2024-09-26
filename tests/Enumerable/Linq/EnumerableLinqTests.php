<?php

/*T/DD*/

declare(strict_types=1);

require_once(__DIR__ . "/../../../vendor/autoload.php");

use Pst\Core\Enumerable\Enumerable;
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

    Should::beTrue($enumerable->sequenceEqual([10=>"ten", 9=>"nine", 8=>"eight", 7=>"seven", 6=>"six", 5=>"five", 4=>"four", 3=>"three", 2=>"two", 1=>"one", 0=>"zero"]));

    Should::beTrue($enumerable->all(fn($v,$k)=>is_string($v) && is_int($k)));
    Should::beFalse($enumerable->all(fn($v,$k)=> $v === "one"));

    Should::beTrue($enumerable->any(fn($v,$k)=> $v === "one"));
    Should::beFalse($enumerable->any(fn($v,$k)=> $v === "eleven"));

    Should::beTrue($enumerable->contains("one"));
    Should::beFalse($enumerable->contains("eleven"));

    Should::beTrue($enumerable->containsKey(1));
    Should::beFalse($enumerable->containsKey(11));

    Should::equal($enumerable->count(), 11);
    Should::equal($enumerable->count(fn($v,$k)=>$k > 5), 5);
    Should::equal($enumerable->count(fn($v,$k)=>$v === "one"), 1);

    Should::equal($enumerable->first(), "ten");
    Should::equal($enumerable->first(fn($v,$k)=>$k < 2), "one");
    Should::throw(InvalidOperationException::class, fn() => $enumerable->first(fn($v,$k)=>$v === "eleven"));

    Should::equal($enumerable->firstOrDefault(), "ten");
    Should::equal($enumerable->firstOrDefault(fn($v,$k)=>$v === "one"), "one");
    Should::equal($enumerable->firstOrDefault(fn($v,$k)=>$v === "eleven"), "");

    Should::equal($enumerable->firstKey(), 10);
    Should::equal($enumerable->firstKey(fn($v,$k)=>$k < 2), 1);
    Should::throw(InvalidOperationException::class, fn() => $enumerable->firstKey(fn($v,$k)=>$k < 0));

    Should::equal($enumerable->firstKeyOrDefault(), 10);
    Should::equal($enumerable->firstKeyOrDefault(fn($v,$k)=>$k < 2), 1);
    Should::equal($enumerable->firstKeyOrDefault(fn($v,$k)=>$k < 0), null);

    Should::equal($enumerable->iterationCount(fn($v,$k)=>$k < 5), 7);

    Should::beTrue($enumerable->keys()->sequenceEqual([10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0]));
    Should::beTrue($enumerable->keys(fn($v,$k)=>$v === "one")->sequenceEqual([1]));

    Should::equal($enumerable->last(), "zero");
    Should::equal($enumerable->last(fn($v,$k)=>$k < 2), "zero");
    Should::throw(InvalidOperationException::class, fn() => $enumerable->last(fn($v,$k)=>$v === "eleven"));

    Should::equal($enumerable->lastOrDefault(), "zero");
    Should::equal($enumerable->lastOrDefault(fn($v,$k)=>$v === "one"), "one");
    Should::equal($enumerable->lastOrDefault(fn($v,$k)=>$v === "eleven"), "");

    Should::equal($enumerable->lastKey(), 0);
    Should::equal($enumerable->lastKey(fn($v,$k)=>$k < 2), 0);
    Should::throw(InvalidOperationException::class, fn() => $enumerable->lastKey(fn($v,$k)=>$k < 0));

    Should::equal($enumerable->lastKeyOrDefault(), 0);
    Should::equal($enumerable->lastKeyOrDefault(fn($v,$k)=>$k < 2), 0);
    Should::equal($enumerable->lastKeyOrDefault(fn($v,$k)=>$k < 0), null); // TODO, BUG, FIX: NOT SURE ABOUT THIS. NEED TO INVESTIGATE


    

    // $enumerator = RewindableEnumerator::create($six);

    // foreach ($enumerator as $key => $value) {
    //     echo "$key => $value\n";
    // }

    // exit;
    // // print_r($testMixedArray);
    // // print_r($enumerator->toArray());

    // var_dump($enumerator->sequenceEqual($testMixedArray));

    // // $enumerator = $enumerator->select(
    // //     fn($value, $key) => !is_string($value) ? $key : $value,
    // //     fn($value, $key) => is_string($value) ? $key : $value
    // // );

    


    
});