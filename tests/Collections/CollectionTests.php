<?php

/*TDD*/

declare(strict_types=1);

require_once(__DIR__ . "/../../vendor/autoload.php");

use Pst\Core\Collections\Collection;
use Pst\Core\Types\Type;

use Pst\Testing\Should;

Should::executeTests(function() {
    $testArray = [
        0,
        "one" => 1,
        2 => 2,
        "three" => 3,
        4 => 4,
        "five" => 5
    ];

    $readOnlyCollection = new Collection($testArray, Type::int());

    Should::equal($testArray[0], $readOnlyCollection[0]);
    Should::equal($testArray["one"], $readOnlyCollection["one"]);
    Should::equal($testArray[2], $readOnlyCollection[2]);
    Should::equal($testArray["three"], $readOnlyCollection["three"]);
    Should::equal($testArray[4], $readOnlyCollection[4]);
    Should::equal($testArray["five"], $readOnlyCollection["five"]);

    // Not Read-only
    Should::notThrow(Exception::class, fn() => $readOnlyCollection[0] = 0);

    // Invalid index
    Should::throw(Exception::class, fn() => $readOnlyCollection[10]);

    Should::beTrue($readOnlyCollection->all(fn($v, $k) => $v >= 0));
    Should::beFalse($readOnlyCollection->all(fn($v, $k) => $v >= 3));    
});