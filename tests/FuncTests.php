<?php

/*TDD*/

declare(strict_types=1);

namespace FuncTests;

use Pst\Core\Func;
use Pst\Core\Types\Type;
use Pst\Core\Types\TypeHint;

use Pst\Testing\Should;

use Exception;
use InvalidArgumentException;

require_once(__DIR__ . "/../vendor/autoload.php");

Should::executeTests(function() {
    // $closure = function(int|float $i, $s) {
    //     return 1;
    // };

    // $closureFunc = Func::new($closure, TypeHint::fromTypeNames("int|float"), Type::string(), Type::bool());
    // print_r($closureFunc);
    // print_r($closureFunc(1, "ASDF"));

    //$closureInfo = Func::closureTypeInfo($closure);
    //print_r($closureInfo);

    Should::notThrow(Exception::class, fn() => Func::new(fn($a, $b, $c) => true, TypeHint::undefined(), TypeHint::undefined(), TypeHint::undefined(), TypeHint::undefined()));
    Should::notThrow(Exception::class, fn() => Func::new(fn($a, $b, $c): bool => true, TypeHint::undefined(), TypeHint::undefined(), TypeHint::undefined(), Type::bool()));
    Should::notThrow(Exception::class, fn() => Func::new(fn($a, $b, float $c): bool => true, TypeHint::undefined(), TypeHint::undefined(), Type::float(), Type::bool()));
    Should::notThrow(Exception::class, fn() => Func::new(fn($a, string $b, float $c): bool => true, TypeHint::undefined(), Type::string(), Type::float(), Type::bool()));
    Should::notThrow(Exception::class, fn() => Func::new(fn(int $a, string $b, float $c): bool => true, Type::int(), Type::string(), Type::float(), Type::bool()));
    Should::notThrow(Exception::class, fn() => Func::new(fn(int $a, string $b, float $c): bool => true, TypeHint::undefined(), TypeHint::undefined(), TypeHint::undefined(), TypeHint::undefined()));

    // Should::throw(InvalidArgumentException::class, fn() => Func::new(fn($a, $b) => true, Type::int(), Type::string(), Type::float(), Type::bool()));
    // Should::throw(InvalidArgumentException::class, fn() => Func::new(fn($a, $b, $c) => true, Type::int(), Type::string(), Type::float(), Type::bool()));
    // Should::throw(InvalidArgumentException::class, fn() => Func::new(fn(int $a, $b, $c) => true, Type::int(), Type::string(), Type::float(), Type::bool()));
    // Should::throw(InvalidArgumentException::class, fn() => Func::new(fn(int $a, string $b, $c) => true, Type::int(), Type::string(), Type::float(), Type::bool()));
    // Should::throw(InvalidArgumentException::class, fn() => Func::new(fn(int $a, string $b, float $c) => true, Type::int(), Type::string(), Type::float(), Type::bool()));
    // Should::throw(InvalidArgumentException::class, fn() => Func::new(fn($a, $b, $c): bool => true, TypeHint::undefined(), TypeHint::undefined(), TypeHint::undefined(), Type::void()));
    // Should::throw(InvalidArgumentException::class, fn() => Func::new(function($a, $b, $c): void {}, TypeHint::undefined(), TypeHint::undefined(), TypeHint::undefined(), Type::void()));
});