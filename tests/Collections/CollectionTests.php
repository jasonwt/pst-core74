<?php

/*TDD*/

declare(strict_types=1);

require_once(__DIR__ . "/../../vendor/autoload.php");

use Pst\Core\Collections\Collection;
use Pst\Core\Types\Type;
use Pst\Core\Types\TypeUnion;
use Pst\Testing\Should;

use function Pst\Core\dd;
use function Pst\Core\pd;

Should::executeTests(function() {
    $testArray = [
        0 => "zero",
        "one" => 1,
        2 => "two",
        "three" => 3,
        4 => "four",
        "five" => 5,
        6 => "six",
        "seven" => 7,
        8 => "eight",
        "nine" => 9,
        10 => "ten"

    ];

    $generator = (function() use ($testArray): Generator {
        foreach ($testArray as $k => $v) {
            yield $k => $v;
        }
    })();

    $collection = new Collection($generator, TypeUnion::create(Type::int(), Type::string()));

    // print_r($collection);
    // $collection->offsetExists(2);
    // print_r($collection);
    // foreach ($collection as $k => $v) {
    //     echo "$k => $v\n";
    // }
    // print_r($collection);

    Should::equal($testArray[0], $collection[0]);
    Should::equal($testArray["one"], $collection["one"]);
    Should::equal($testArray[2], $collection[2]);
    Should::equal($testArray["three"], $collection["three"]);
    Should::equal($testArray[4], $collection[4]);
    Should::equal($testArray["five"], $collection["five"]);

    // Not Read-only
    Should::notThrow(Exception::class, fn() => $collection[0] = 0);

    // Invalid index
    Should::throw(Exception::class, fn() => $collection[11]);

    // Should::beTrue($collection->all(fn($v) => $v >= 0));
    // Should::beFalse($collection->all(fn($v) => $v > 10));

    $collection->add(11, "eleven");

    // foreach ($collection as $k => $v) {
    //     echo "$k => $v\n";
    // }
});