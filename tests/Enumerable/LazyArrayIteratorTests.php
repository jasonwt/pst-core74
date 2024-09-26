<?php

/*TD/D*/

declare(strict_types=1);

//namespace Pst\Core\Tests\DependencyInjection;

require_once(__DIR__ . "/../../vendor/autoload.php");

use Pst\Core\CoreObject;
use Pst\Core\Types\Type;
use Pst\Core\Enumerable\Enumerator;
use Pst\Core\Enumerable\IImmutableEnumerable;
use Pst\Core\Enumerable\Iterators\LazyArrayAccessIterator;
use Pst\Core\Enumerable\Iterators\LazyArrayIterator;
use Pst\Core\Enumerable\Iterators\LazyRewindableEnumerableTrait;
use Pst\Core\Interfaces\ICoreObject;
use Pst\Testing\Should;

use function Pst\Core\dd;

//use Exception;

// class LazyArrayIterator extends CoreObject implements ICoreObject, IteratorAggregate {
//     use LazyRewindableEnumerableTrait;


// }



Should::executeTests(function() {
    $testArray = [
        "five" => 0,
        4 => "one",
        "three" => 2,
        2 => "three",
        "one" => 4,
        0 => "zero"
    ];


//     $iterator = new LazyArrayIterator($testArray);

//     print_r($iterator);



//     foreach ($iterator as $key => $value) {
//         echo "Key: $key, Value: $value\n";
//         echo "O:key: " . $iterator->key() . ": " . $iterator->current() . "\n";
//         $iterator->next();
//         foreach ($iterator as $k2 => $v2) {
//             echo "\t\tKey: $k2, Value: $v2\n";
            
//         }

//         //print_r($iterator);
//         //break;
        
//     }

//     // echo "\n";

//     // foreach ($iterator as $key => $value) {
//     //     echo "Key: $key, Value: $value\n";
//     // }

//     // print_r($iterator);


// //     $newTestArray = fn(): IImmutableEnumerable => Enumerator::create($testArray, Type::int());

// //     $iterator = new LazyArrayIterator($newTestArray());

// // //    print_r($iterator);

// // //    $iterator->next();
// // //    print_r($iterator);

// // //    $iterator->next();
// // //    print_r($iterator);

// // ///    $iterator->next();
// // //    print_r($iterator);

// //     foreach ($iterator as $key => $value) {
// //         echo "Key: $key, Value: $value\n";
// //     }

// //     echo "\n";

// //     foreach ($iterator as $key => $value) {
// //         echo "Key: $key, Value: $value\n";
// //     }

// //     //print_r($iterator);
});