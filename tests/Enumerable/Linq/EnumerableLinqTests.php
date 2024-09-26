<?php

/*T/DD*/

declare(strict_types=1);

//namespace Pst\Core\Tests\DependencyInjection;

require_once(__DIR__ . "/../../../vendor/autoload.php");

use Pst\Core\Enumerable\Enumerator;
use Pst\Core\Enumerable\Iterators\RewindableIterator;
use Pst\Core\Enumerable\Linq\Linq;
use Pst\Core\Enumerable\RewindableEnumerator;
use Pst\Testing\Should;


class IteratorClass implements Iterator {
    private Iterator $iterator;

    public function __construct() {
        $this->iterator = new ArrayIterator(range(0, 10));
    }
    public function current() {
        return $this->iterator->current();
    }
    public function key() {
        return $this->iterator->key();
    }
    public function next(): void {
        $this->iterator->next();
    }
    public function rewind(): void {
        $this->iterator->rewind();
    }
    public function valid(): bool {
        return $this->iterator->valid();
    }
}

$iteratorClass = new IteratorClass();

class AggregateClass1 implements IteratorAggregate {
    private Iterator $subIterator;

    public function __construct(Iterator $subIterator) {
        $this->subIterator = $subIterator;
    }

    public function getIterator(): Iterator {
        yield $this->subIterator;
    }
}

$subClass1 = new AggregateClass1($iteratorClass);

class AggregateClass2 implements IteratorAggregate {
    private IteratorAggregate $subIterator;

    public function __construct(IteratorAggregate $subIterator) {
        $this->subIterator = $subIterator;
    }

    public function getIterator(): IteratorAggregate {
        return $this->subIterator;
    }
}

$subClass2 = new AggregateClass2($subClass1);

class AggregateClass3 implements IteratorAggregate {
    private IteratorAggregate $subIterator;

    public function __construct(IteratorAggregate $subIterator) {
        $this->subIterator = $subIterator;
    }

    public function getIterator(): IteratorAggregate {
        return $this->subIterator;
    }
}

$subClass3 = new AggregateClass2($subClass2);

$baseIterator = $subClass3;

while (true) {
    if ($baseIterator instanceof IteratorAggregate) {
        echo get_class($baseIterator) . " implements IteratorAggregate\n";
        $baseIterator = $baseIterator->getIterator();
    } else {

        break;
    }
}




foreach ($baseIterator as $key => $value) {
    echo "$key => $value\n";
}

exit;



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






    // // $two = (fn(): Generator => yield (fn(): Generator => yield from (fn(): Generator => yield from (fn(): Generator => yield from (fn(): Generator => yield from (fn(): Generator => yield from $testMixedArray)())())())())())();
    // // $three = (fn(): Generator => yield from (fn(): Generator => yield from (fn(): Generator => yield from (fn(): Generator => yield from (fn(): Generator => yield from $testMixedArray)())())())())();
    // // $four = (fn(): Generator => yield from (fn(): Generator => yield from (fn(): Generator => yield from (fn(): Generator => yield from $testMixedArray)())())())();
    // // $five = (fn(): Generator => yield from (fn(): Generator => yield from (fn(): Generator => yield from $testMixedArray)())())();

    // $six = (fn(): Generator => yield from 
    //     (fn(): Generator => yield from $testMixedArray)()
    // )();


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