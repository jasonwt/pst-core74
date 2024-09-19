<?php

/*TDD*/

declare(strict_types=1);

//namespace Pst\Core\Tests\DependencyInjection;

require_once(__DIR__ . "/../../vendor/autoload.php");

use Pst\Core\Types\Type;
use Pst\Core\Collections\Enumerator;
use Pst\Core\Collections\IEnumerable;
use Pst\Core\Collections\ReadOnlyCollection;
use Pst\Testing\Should;

use function Pst\Core\dd;

//use Exception;


Should::executeTests(function() {
    $testArray = [
        0,
        "one" => 1,
        2 => 2,
        "three" => 3,
        4 => 4,
        "five" => 5
    ];

    $readOnlyCollection = new ReadOnlyCollection($testArray, Type::int());

    Should::equal($testArray[0], $readOnlyCollection[0]);
    Should::equal($testArray["one"], $readOnlyCollection["one"]);
    Should::equal($testArray[2], $readOnlyCollection[2]);
    Should::equal($testArray["three"], $readOnlyCollection["three"]);
    Should::equal($testArray[4], $readOnlyCollection[4]);
    Should::equal($testArray["five"], $readOnlyCollection["five"]);

    // Read-only
    Should::throw(Exception::class, fn() => $readOnlyCollection[0] = 0);

    // Invalid index
    Should::throw(Exception::class, fn() => $readOnlyCollection[10]);

    Should::beTrue($readOnlyCollection->all(fn($v, $k) => $v >= 0));
    Should::beFalse($readOnlyCollection->all(fn($v, $k) => $v >= 3));

//     $newTestArray = fn(): IEnumerable => Enumerator::new($testArray, Type::int());

//     Should::equal($testArray, $newTestArray()->toArray());

//     Should::beTrue($newTestArray()->all(fn($v, $k) => $v >= 0));
//     Should::beFalse($newTestArray()->all(fn($v, $k) => $v >= 3));

//     Should::beTrue($newTestArray()->any(fn($v, $k) => $v >= 3));
//     Should::beFalse($newTestArray()->any(fn($v, $k) => $v >= 6));

//     Should::equal(6, $newTestArray()->count());
//     Should::equal(3, $newTestArray()->count(fn($v, $k) => $v >= 3));
//     Should::equal(3, $newTestArray()->count(fn($v, $k) => is_string($k)));

//     Should::equal(0, $newTestArray()->first());
//     Should::equal(3, $newTestArray()->first(fn($v, $k) => $v >= 3));
//     Should::equal(1, $newTestArray()->first(fn($v, $k) => is_string($k)));

//     Should::equal(5, $newTestArray()->last());
//     Should::equal(3, $newTestArray()->last(fn($v, $k) => $v <= 3));
//     Should::equal(4, $newTestArray()->last(fn($v, $k) => !is_string($k)));
    
// //    print_r($newTestArray()->orderBy(fn($v, $k) => $k)->toArray());

//     Should::equal([0=>0,"one"=>2,2=>4,"three"=>6, 4=>8,"five"=>10], $newTestArray()->select(fn($x, $k) => $x * 2)->toArray());
//     Should::equal([0=>0,"one"=>"one",2=>2,"three"=>"three",4=>4,"five"=>"five"], $newTestArray()->select(fn($x, $k) => $k)->toArray());

//     // Should::equal($newTestArray()->sequenceEqual($newTestArray()));

//     Should::equal([2=>2,"three"=>3,4=>4,"five"=>5], $newTestArray()->skip(2)->toArray());

//     Should::equal([2=>2,"three"=>3,4=>4,"five"=>5], $newTestArray()->skipWhile(fn($v, $k) => $v < 2)->toArray());
//     Should::equal(["three"=>3,4=>4,"five"=>5], $newTestArray()->skipWhile(fn($v, $k) => $k !== "three")->toArray());

//     Should::equal([0=>0,"one"=>1,2=>2], $newTestArray()->take(3)->toArray());

//     Should::equal([0=>0,"one"=>1,2=>2], $newTestArray()->takeWhile(fn($v, $k) => $v < 3)->toArray());
//     Should::equal([0=>0,"one"=>1,2=>2], $newTestArray()->takeWhile(fn($v, $k) => $k !== "three")->toArray());

//     Should::equal($testArray, $newTestArray()->where(fn($v, $k) => true)->toArray());
//     Should::equal([0=>0,2=>2,4=>4], $newTestArray()->where(fn($v, $k) => $v % 2 === 0)->toArray());
//     Should::equal(["one"=>1,"three"=>3,"five"=>5], $newTestArray()->where(fn($v, $k) => is_string($k))->toArray());

//     Should::equal([0,1,2,3,4,5], $newTestArray()->toArray(fn($v, $k) => $v));

    
//     //print_r($results);

    
});