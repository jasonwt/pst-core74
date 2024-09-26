<?php

/*T/DD*/

declare(strict_types=1);

require_once(__DIR__ . "/../../vendor/autoload.php");

use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Enumerable\Enumerable;
use Pst\Core\Enumerable\IEnumerable;
use Pst\Core\Enumerable\IFloatEnumerable;
use Pst\Core\Enumerable\IScalerEnumerable;
use Pst\Core\Enumerable\IStringEnumerable;
use Pst\Core\Enumerable\IBooleanEnumerable;
use Pst\Core\Enumerable\IIntegerEnumerable;
use Pst\Core\Enumerable\INumericEnumerable;

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

    Should::equal("int|string", (string) Enumerable::determineTypeHint($mixedTestArray));

    $mkMixedGenerator = fn() => (function() use ($mixedTestArray) {
        foreach ($mixedTestArray as $key => $value) {
            yield $key => $value;
        }
    })();

    $enumerable = Enumerable::create($mkMixedGenerator());
    Should::beA($enumerable, IEnumerable::class);
    Should::beFalse($enumerable->isRewindable());

    // Testing creation and getIterator() method
    foreach (($enumerable = Enumerable::create($mkMixedGenerator())) as $key => $value) {
        Should::equal($mixedTestArray[$key], $value);
        Should::equal($key, $enumerable->getIterator()->key());
        Should::equal($value, $enumerable->getIterator()->current());
        Should::equal($enumerable->getIterator()->valid(), true);
    }
    Should::equal($enumerable->getIterator()->valid(), false);
    // Not rewindable so an exception should be thrown
    Should::throw(Exception::class, (fn() => $enumerable->getIterator()->rewind()));

    // Testing IStringEnumerable interface
    $stringEnumerable = Enumerable::create(array_fill(0, 10, "test"), TypeHintFactory::string());
    Should::beA($stringEnumerable, IEnumerable::class, IStringEnumerable::class, IScalerEnumerable::class);
    Should::notBe($stringEnumerable, INumericEnumerable::class, IIntegerEnumerable::class, IFloatEnumerable::class, IBooleanEnumerable::class);

    // Testing IIntegerEnumerable interface
    $intEnumerable = Enumerable::create(range(0, 10), TypeHintFactory::int());
    Should::beA($intEnumerable, IEnumerable::class, INumericEnumerable::class, IIntegerEnumerable::class, IScalerEnumerable::class);
    Should::notBe($intEnumerable, IStringEnumerable::class, IFloatEnumerable::class, IBooleanEnumerable::class);

    // Testing IFloatEnumerable interface
    $floatEnumerable = Enumerable::create(array_map(fn($v) => (float) $v, range(0, 10)), TypeHintFactory::float());
    Should::beA($floatEnumerable, IEnumerable::class, INumericEnumerable::class, IFloatEnumerable::class, IScalerEnumerable::class);
    Should::notBe($floatEnumerable, IStringEnumerable::class, IIntegerEnumerable::class, IBooleanEnumerable::class);

    // Testing IBooleanEnumerable interface
    $boolEnumerable = Enumerable::create(array_map(fn($v) => $v % 2 === 0, range(0, 10)), TypeHintFactory::bool());
    Should::beA($boolEnumerable, IEnumerable::class, IBooleanEnumerable::class, IScalerEnumerable::class);
    Should::notBe($boolEnumerable, IStringEnumerable::class, INumericEnumerable::class, IIntegerEnumerable::class, IFloatEnumerable::class);

    // Testing Enumerable::range() method (all parameters are integers)
    $intRangeEnumerable = Enumerable::range(0, 10);
    Should::beA($intRangeEnumerable, IEnumerable::class, INumericEnumerable::class, IIntegerEnumerable::class, IScalerEnumerable::class);
    Should::notBe($intRangeEnumerable, IStringEnumerable::class, IFloatEnumerable::class, IBooleanEnumerable::class);
    Should::beTrue($intRangeEnumerable->sequenceEqual([0,1,2,3,4,5,6,7,8,9]));

    // Testing Enumerable::linspace() method (step parameter is a float)
    $floatRangeEnumerable = Enumerable::range(0, 10, 0.5);
    Should::beA($floatRangeEnumerable, IEnumerable::class, INumericEnumerable::class, IFloatEnumerable::class, IScalerEnumerable::class);
    Should::notBe($floatRangeEnumerable, IStringEnumerable::class, IIntegerEnumerable::class, IBooleanEnumerable::class);
    Should::beTrue($floatRangeEnumerable->sequenceEqual([0.0,0.5,1.0,1.5,2.0,2.5,3.0,3.5,4.0,4.5]));

    // Testing Enumerable::repeat() method (string value)
    $stringRepeatEnumerable = Enumerable::repeat("test", 10);
    Should::beA($stringRepeatEnumerable, IEnumerable::class, IStringEnumerable::class, IScalerEnumerable::class);
    Should::notBe($stringRepeatEnumerable, INumericEnumerable::class, IIntegerEnumerable::class, IFloatEnumerable::class, IBooleanEnumerable::class);
    Should::beTrue($stringRepeatEnumerable->sequenceEqual(array_fill(0, 10, "test")));

    // Testing Enumerable::repeat() method (integer value)
    $intRepeatEnumerable = Enumerable::repeat(10, 10);
    Should::beA($intRepeatEnumerable, IEnumerable::class, INumericEnumerable::class, IIntegerEnumerable::class, IScalerEnumerable::class);
    Should::notBe($intRepeatEnumerable, IStringEnumerable::class, IFloatEnumerable::class, IBooleanEnumerable::class);
    Should::beTrue($intRepeatEnumerable->sequenceEqual(array_fill(0, 10, 10)));

    // Testing Enumerable::repeat() method (float value)
    $floatRepeatEnumerable = Enumerable::repeat(10.5, 10);
    Should::beA($floatRepeatEnumerable, IEnumerable::class, INumericEnumerable::class, IFloatEnumerable::class, IScalerEnumerable::class);
    Should::notBe($floatRepeatEnumerable, IStringEnumerable::class, IIntegerEnumerable::class, IBooleanEnumerable::class);
    Should::beTrue($floatRepeatEnumerable->sequenceEqual(array_fill(0, 10, 10.5)));

    // Testing Enumerable::repeat() method (boolean value)
    $boolRepeatEnumerable = Enumerable::repeat(true, 10);
    Should::beA($boolRepeatEnumerable, IEnumerable::class, IBooleanEnumerable::class, IScalerEnumerable::class);
    Should::notBe($boolRepeatEnumerable, IStringEnumerable::class, INumericEnumerable::class, IIntegerEnumerable::class, IFloatEnumerable::class);
    Should::beTrue($boolRepeatEnumerable->sequenceEqual(array_fill(0, 10, true)));

    // Testing Enumerable::linspace() method (start and stop are integers)
    $intLinspaceEnumerable = Enumerable::linspace(0, 5, 2);
    Should::beA($intLinspaceEnumerable, IEnumerable::class, INumericEnumerable::class, IIntegerEnumerable::class, IScalerEnumerable::class);
    Should::notBe($intLinspaceEnumerable, IStringEnumerable::class, IFloatEnumerable::class, IBooleanEnumerable::class);
    //Should::beTrue($intLinspaceEnumerable->sequenceEqual([0.0, 0.1, 0.2, 0.3, 0.4, 0.5])); // BUG, TODO, FIX

    



//     $testArray = [
//         0,
//         "one" => 1,
//         2 => 2,
//         "three" => 3,
//         4 => 4,
//         "five" => 5
//     ];

//     $generator = (function() use ($testArray) {
//         foreach ($testArray as $key => $value) {
//             yield $key => $value;
//         }
//     })();


//     $generator = new Enumerator($generator);

//     // print_r(Linq::toArray($generator));
//     // print_r(Linq::toArray($generator));


//     //$generator = new LazyArrayIterator($generator);

//     while ($generator->valid()) {
//         $key = $generator->key();
//         $value = $generator->current();

//         echo "$key => $value\n";

//         foreach ($generator->getIterator() as $key2 => $value2) {
//             echo "    $key2 => $value2\n";
//         }

//         $generator->next();
//     }

//     // $enumerator = Enumerator::create($generator, TypeHintFactory::mixed());
//     // $enumerator = $enumerator->where(fn($v, $k) => $v > 2);

//     // print_r($enumerator->toArray());
//     // print_r($enumerator->toArray());

//     // foreach ($enumerator as $key => $value) {
//     //     echo "$key => $value\n";
//     // }

//     // echo "\n";

//     // foreach ($enumerator as $key => $value) {
//     //     echo "$key => $value\n";
//     // }

    

// //     $newTestArray = fn(): IImmutableEnumerable => Enumerator::create($testArray, Type::int());

// //     Should::equal($testArray, $newTestArray()->toArray());

// //     Should::beTrue($newTestArray()->all(fn($v, $k) => $v >= 0));
// //     Should::beFalse($newTestArray()->all(fn($v, $k) => $v >= 3));

// //     Should::beTrue($newTestArray()->any(fn($v, $k) => $v >= 3));
// //     Should::beFalse($newTestArray()->any(fn($v, $k) => $v >= 6));

// //     Should::equal(6, $newTestArray()->count());
// //     Should::equal(3, $newTestArray()->count(fn($v, $k) => $v >= 3));
// //     Should::equal(3, $newTestArray()->count(fn($v, $k) => is_string($k)));

// //     Should::equal(0, $newTestArray()->first());
// //     Should::equal(3, $newTestArray()->first(fn($v, $k) => $v >= 3));
// //     Should::equal(1, $newTestArray()->first(fn($v, $k) => is_string($k)));

// //     Should::equal(5, $newTestArray()->last());
// //     Should::equal(3, $newTestArray()->last(fn($v, $k) => $v <= 3));
// //     Should::equal(4, $newTestArray()->last(fn($v, $k) => !is_string($k)));
    
// // //    print_r($newTestArray()->orderBy(fn($v, $k) => $k)->toArray());

// //     Should::equal([0=>0,"one"=>2,2=>4,"three"=>6, 4=>8,"five"=>10], $newTestArray()->select(fn($x, $k) => $x * 2)->toArray());
// //     Should::equal([0=>0,"one"=>"one",2=>2,"three"=>"three",4=>4,"five"=>"five"], $newTestArray()->select(fn($x, $k) => $k)->toArray());

// //     // Should::equal($newTestArray()->sequenceEqual($newTestArray()));

// //     Should::equal([2=>2,"three"=>3,4=>4,"five"=>5], $newTestArray()->skip(2)->toArray());

// //     Should::equal([2=>2,"three"=>3,4=>4,"five"=>5], $newTestArray()->skipWhile(fn($v, $k) => $v < 2)->toArray());
// //     Should::equal(["three"=>3,4=>4,"five"=>5], $newTestArray()->skipWhile(fn($v, $k) => $k !== "three")->toArray());

// //     Should::equal([0=>0,"one"=>1,2=>2], $newTestArray()->take(3)->toArray());

// //     Should::equal([0=>0,"one"=>1,2=>2], $newTestArray()->takeWhile(fn($v, $k) => $v < 3)->toArray());
// //     Should::equal([0=>0,"one"=>1,2=>2], $newTestArray()->takeWhile(fn($v, $k) => $k !== "three")->toArray());

// //     Should::equal($testArray, $newTestArray()->where(fn($v, $k) => true)->toArray());
// //     Should::equal([0=>0,2=>2,4=>4], $newTestArray()->where(fn($v, $k) => $v % 2 === 0)->toArray());
// //     Should::equal(["one"=>1,"three"=>3,"five"=>5], $newTestArray()->where(fn($v, $k) => is_string($k))->toArray());

// //     Should::equal([0,1,2,3,4,5], $newTestArray()->toArray(fn($v, $k) => $v));

    
// //     //print_r($results);

    
});