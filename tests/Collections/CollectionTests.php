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

    $generator = (function() use ($testArray): Traversable {
        foreach ($testArray as $k => $v) {
            yield $k => $v;
        }
    })();

    $readOnlyCollection = new Collection($generator, TypeUnion::new(Type::int(), Type::string()));

    // print_r($readOnlyCollection);
    // $readOnlyCollection->offsetExists(2);
    // print_r($readOnlyCollection);
    // foreach ($readOnlyCollection as $k => $v) {
    //     echo "$k => $v\n";
    // }
    // print_r($readOnlyCollection);

    Should::equal($testArray[0], $readOnlyCollection[0]);
    Should::equal($testArray["one"], $readOnlyCollection["one"]);
    Should::equal($testArray[2], $readOnlyCollection[2]);
    Should::equal($testArray["three"], $readOnlyCollection["three"]);
    Should::equal($testArray[4], $readOnlyCollection[4]);
    Should::equal($testArray["five"], $readOnlyCollection["five"]);

    // Not Read-only
    Should::notThrow(Exception::class, fn() => $readOnlyCollection[0] = 0);

    // Invalid index
    Should::throw(Exception::class, fn() => $readOnlyCollection[11]);

    // Should::beTrue($readOnlyCollection->all(fn($v) => $v >= 0));
    // Should::beFalse($readOnlyCollection->all(fn($v) => $v > 10));    
});