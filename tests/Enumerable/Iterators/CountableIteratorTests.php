<?php

/*TDD*/

declare(strict_types=1);

require_once(__DIR__ . "/../../../vendor/autoload.php");

use Pst\Core\Enumerable\Enumerable;
use Pst\Core\Enumerable\IRewindableEnumerable;
use Pst\Core\Enumerable\Iterators\CountableIterator;
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

    $countableIterator = new CountableIterator($mkMixedGenerator());

    

    
});