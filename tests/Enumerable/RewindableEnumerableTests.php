<?php

/*TDD*/

declare(strict_types=1);

require_once(__DIR__ . "/../../vendor/autoload.php");

use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Enumerable\IEnumerable;
use Pst\Core\Enumerable\IFloatEnumerable;
use Pst\Core\Enumerable\IBooleanEnumerable;
use Pst\Core\Enumerable\IIntegerEnumerable;
use Pst\Core\Enumerable\INumericEnumerable;
use Pst\Core\Enumerable\IRewindableEnumerable;
use Pst\Core\Enumerable\IScalerEnumerable;
use Pst\Core\Enumerable\IStringEnumerable;
use Pst\Core\Enumerable\RewindableEnumerable;

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
    Should::beA($enumerable, IEnumerable::class, IRewindableEnumerable::class);
    Should::beTrue($enumerable->isRewindable());

    // Testing creation and getIterator() method
    foreach (($enumerable) as $key => $value) {
        Should::equal($mixedTestArray[$key], $value);
        Should::equal($key, $enumerable->getIterator()->key());
        Should::equal($value, $enumerable->getIterator()->current());
        Should::equal($enumerable->getIterator()->valid(), true);
    }
    Should::equal($enumerable->getIterator()->valid(), false);

    

    // Is rewindable so no exception should be thrown
    Should::notThrow(Exception::class, (fn() => $enumerable->getIterator()->rewind()));

    // Testing IRewindableEnumerable / IStringEnumerable interface
    $stringEnumerable = RewindableEnumerable::create(array_fill(0, 10, "test"), TypeHintFactory::string());
    Should::beA($stringEnumerable, IEnumerable::class, IStringEnumerable::class, IScalerEnumerable::class, IRewindableEnumerable::class);
    Should::notBe($stringEnumerable, INumericEnumerable::class, IIntegerEnumerable::class, IFloatEnumerable::class, IBooleanEnumerable::class);

    // Testing IRewindableEnumerable / IIntegerEnumerable interface
    $intEnumerable = RewindableEnumerable::create(range(0, 10), TypeHintFactory::int());
    Should::beA($intEnumerable, IEnumerable::class, INumericEnumerable::class, IIntegerEnumerable::class, IScalerEnumerable::class, IRewindableEnumerable::class);
    Should::notBe($intEnumerable, IStringEnumerable::class, IFloatEnumerable::class, IBooleanEnumerable::class);

    // Testing IRewindableEnumerable / IFloatEnumerable interface
    $floatEnumerable = RewindableEnumerable::create(array_map(fn($v) => (float) $v, range(0, 10)), TypeHintFactory::float());
    Should::beA($floatEnumerable, IEnumerable::class, INumericEnumerable::class, IFloatEnumerable::class, IScalerEnumerable::class, IRewindableEnumerable::class);
    Should::notBe($floatEnumerable, IStringEnumerable::class, IIntegerEnumerable::class, IBooleanEnumerable::class);

    // Testing IRewindableEnumerable / IBooleanEnumerable interface
    $boolEnumerable = RewindableEnumerable::create(array_map(fn($v) => $v % 2 === 0, range(0, 10)), TypeHintFactory::bool());
    Should::beA($boolEnumerable, IEnumerable::class, IBooleanEnumerable::class, IScalerEnumerable::class, IRewindableEnumerable::class);
    Should::notBe($boolEnumerable, IStringEnumerable::class, INumericEnumerable::class, IIntegerEnumerable::class, IFloatEnumerable::class); 
});