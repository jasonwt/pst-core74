<?php

/*TDD*/

declare(strict_types=1);

namespace FuncTests;

use Pst\Core\Func;
use Pst\Core\Types\TypeHintFactory;

use Pst\Testing\Should;

use Exception;
use InvalidArgumentException;

use function Pst\Core\dd;

require_once(__DIR__ . "/../vendor/autoload.php");

Should::executeTests(function() {

    // When strict types checking is disabled, we can used undefined parameter types
    Func::disableStrictTypes();

    // We throw here because Func can not have void as a return type, use Action instead
    Should::throw(
        InvalidArgumentException::class, 
        fn() => Func::new(function($a, $b, $c): void {}, TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::void())
    );

    // Throws here because the defined Closures has more parameters than the defined expected parameter types
    Should::throw(
        InvalidArgumentException::class, 
        fn() => Func::new(fn($a, $b, $c) => true, TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined())
    );

    // Should throw because there are more expected parameter types specified then the closure has and none of the expected specified parameters types are optional. 
    // see Func::optionalParameter(ITypeHint $typeHint): ITypeHint
    Should::throw(
        InvalidArgumentException::class, 
        fn() => Func::new(fn($a, $b) => true, TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined())
    );

    // Should throw because the Closure was defined with parameter a's type of int but we are expecting a string as its parameter type
    Should::throw(
        InvalidArgumentException::class, 
        fn() => Func::new(fn(int $a, float $b) => true, TypeHintFactory::string(), TypeHintFactory::undefined(), TypeHintFactory::undefined())
    );

    Func::new(fn(int $a, float $b) => true, TypeHintFactory::int(), TypeHintFactory::float(), TypeHintFactory::string());



    // Even though no parameter types are defined in either the Closure or the expected parameter types, the Func::new function does not throw an exception because we have disabled strict types checking
    $func = Should::notThrow(
        Exception::class, 
        fn() => Func::new(fn($a, $b, $c) => true, TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined())
    )[0];

    // Should not throw here because expected parameter types that are specified as null as promoted to undefined and strict types checking is disabled
    $func = Should::notThrow(
        Exception::class, 
        fn() => Func::new(fn($a, $b) => true, null, null, null)
    )[0];

    // Should not throw because the last parameter is no optional
    $func = Should::notThrow(
        Exception::class, 
        fn() => Func::new(fn($a, $b) => true, TypeHintFactory::undefined(), TypeHintFactory::undefined(), Func::optionalParameter(TypeHintFactory::undefined()), TypeHintFactory::undefined())
    )[0];

    // Should not throw when instantiating the Func because the Closure return type is not defined and we have to defer checking the return type when it is actually called
    $func = Should::notThrow(
        Exception::class, 
        fn() => Func::new(fn(int $a, float $b) => true, TypeHintFactory::int(), TypeHintFactory::float(), TypeHintFactory::float())
    )[0];
    
    // Whe should fair here though because we are returning a bool and have the expected return type set to float
    Should::throw(InvalidArgumentException::class, fn() => $func(1, 1.0));
    

    

    

    // Func::enableStrictTypes();
    // // Fails because the defined Closures has more parameters than the defined type hints
    // Should::throw(
    //     InvalidArgumentException::class, 
    //     fn() => Func::new(fn($a, $b, $c) => true, TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined())
    // );
    // // Fails because strict types is enabled and neighter the closure nor the type hints have defined the expected types
    // Should::throw(
    //     InvalidArgumentException::class, 
    //     fn() => Func::new(fn($a, $b, $c) => true, TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined())
    // );

    // // Does not fail because the Func::new function is provided with the expected parameter and return types
    // $func = Should::notThrow(
    //     Exception::class, 
    //     fn() => Func::new(fn($a, $b, $c) => true, TypeHintFactory::int(), TypeHintFactory::string(), TypeHintFactory::float(), TypeHintFactory::bool())
    // )[0];

    // dd($func);

    // // Does not fail because the Closure definition provides the expected parameter and return types
    // $func = Should::notThrow(
    //     Exception::class, 
    //     fn() => Func::new(fn(int $a, string $b, float $c): bool => true, TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined())
    // )[0];

    // // Does not fail because between the Closure definition and the type hints, the expected parameter and return types are provided
    // $func = Should::notThrow(
    //     Exception::class, 
    //     fn() => Func::new(fn(int $a, $b, float $c) => true, TypeHintFactory::undefined(), TypeHintFactory::string(), TypeHintFactory::undefined(), TypeHintFactory::float())
    // )[0];

    // dd($func);
    

    // // $closure = function(int|float $i, $s) {
    // //     return 1;
    // // };

    // // $closureFunc = Func::new($closure, TypeHintFactory::tryParseTypeName("int|float"), Type::string(), Type::bool());
    // // print_r($closureFunc);
    // // print_r($closureFunc(1, "ASDF"));

    // //$closureInfo = Func::closureTypeInfo($closure);
    // //print_r($closureInfo);

    // Should::notThrow(Exception::class, fn() => Func::new(fn($a, $b, $c) => true, TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined()));
    // Should::notThrow(Exception::class, fn() => Func::new(fn($a, $b, $c): bool => true, TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined(), Type::bool()));
    // Should::notThrow(Exception::class, fn() => Func::new(fn($a, $b, float $c): bool => true, TypeHintFactory::undefined(), TypeHintFactory::undefined(), Type::float(), Type::bool()));
    // Should::notThrow(Exception::class, fn() => Func::new(fn($a, string $b, float $c): bool => true, TypeHintFactory::undefined(), Type::string(), Type::float(), Type::bool()));
    // Should::notThrow(Exception::class, fn() => Func::new(fn(int $a, string $b, float $c): bool => true, Type::int(), Type::string(), Type::float(), Type::bool()));
    // Should::notThrow(Exception::class, fn() => Func::new(fn(int $a, string $b, float $c): bool => true, TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined()));

    // // Should::throw(InvalidArgumentException::class, fn() => Func::new(fn($a, $b) => true, Type::int(), Type::string(), Type::float(), Type::bool()));
    // // Should::throw(InvalidArgumentException::class, fn() => Func::new(fn($a, $b, $c) => true, Type::int(), Type::string(), Type::float(), Type::bool()));
    // // Should::throw(InvalidArgumentException::class, fn() => Func::new(fn(int $a, $b, $c) => true, Type::int(), Type::string(), Type::float(), Type::bool()));
    // // Should::throw(InvalidArgumentException::class, fn() => Func::new(fn(int $a, string $b, $c) => true, Type::int(), Type::string(), Type::float(), Type::bool()));
    // // Should::throw(InvalidArgumentException::class, fn() => Func::new(fn(int $a, string $b, float $c) => true, Type::int(), Type::string(), Type::float(), Type::bool()));
    // // Should::throw(InvalidArgumentException::class, fn() => Func::new(fn($a, $b, $c): bool => true, TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined(), SpecialType::void()));
    // // Should::throw(InvalidArgumentException::class, fn() => Func::new(function($a, $b, $c): void {}, TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined(), SpecialType::void()));
});