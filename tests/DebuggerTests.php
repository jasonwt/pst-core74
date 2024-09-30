<?php

/*T/DD*/

declare(strict_types=1);

//namespace Pst\Core\Tests\DependencyInjection;

require_once(__DIR__ . "/../vendor/autoload.php");

use Pst\Core\Debugger;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Enumerable\Enumerable;
use Pst\Core\Enumerable\Iterators\LazyArrayIterator;
use Pst\Testing\Should;

$stringInput = "this is a test string";
$intInput = 5;
$floatInput = (float) 5;
$falseInput = false;
$trueInput = true;
$nullInput = null;

$inputsArray = [
    "stringValue" => $stringInput,
    "intValue" => $intInput,
    "floatValue" => $floatInput,
    "falseValue" => $falseInput,
    "trueValue" => $trueInput,
    "nullValue" => $nullInput,
];

Should::executeTests(function() use ($inputsArray){
    $input = $inputsArray + [
        "arrayValue" => $inputsArray
    ];

    $input = [
        "arrayValue" => [
            "stringValue" => "this is a test string",
            "intValue" => 5,
        ]
    ];

    echo "\n\n";
    print_r(Debugger::debug($input));
    echo "\n\n";

    
});