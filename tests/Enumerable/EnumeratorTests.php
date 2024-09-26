<?php

/*T/DD*/

declare(strict_types=1);

//namespace Pst\Core\Tests\DependencyInjection;

require_once(__DIR__ . "/../../vendor/autoload.php");

use Pst\Core\CoreObject;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Enumerable\Enumerator;
use Pst\Core\Enumerable\IEnumerable;
use Pst\Core\Enumerable\IEnumerator;
use Pst\Core\Enumerable\Iterators\LazyArrayIterator;
use Pst\Core\Enumerable\Linq\Linq;
use Pst\Core\Enumerable\Iterators\RewindableIterator;
use Pst\Core\Enumerable\Linq\EnumerableLinqTrait;
use Pst\Core\Enumerable\RewindableIteratorAggregate;
use Pst\Core\Exceptions\NotImplementedException;
use Pst\Core\Types\ITypeHint;
use Pst\Testing\Should;


Should::executeTests(function() {
    $testMixedArray = [
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