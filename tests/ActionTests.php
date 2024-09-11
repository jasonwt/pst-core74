<?php

/*TDD*/

declare(strict_types=1);

namespace ActionTests;

use Pst\Core\Types\TypeHint;

use Pst\Core\Action;

use Pst\Testing\Should;

use Exception;
use InvalidArgumentException;

require_once(__DIR__ . "/../vendor/autoload.php");

Should::executeTests(function() {
    Should::throw(
        InvalidArgumentException::class, 
        fn() => Action::new(function($a, $b, $c) {}, TypeHint::undefined(), TypeHint::undefined()),
        fn() => Action::new(function($a, $b, $c) {}, TypeHint::undefined(), TypeHint::undefined(), TypeHint::undefined(), TypeHint::undefined()),
        fn() => Action::new(function($a, $b, $c): bool { return true;}, TypeHint::undefined(), TypeHint::undefined()),
        fn() => Action::new(function($a, $b, $c): bool { return true;}, TypeHint::undefined(), TypeHint::undefined(), TypeHint::undefined()),
        fn() => Action::new(function($a, $b, $c): bool { return true;}, TypeHint::undefined(), TypeHint::undefined(), TypeHint::undefined(), TypeHint::undefined()),
    );

    Action::disableValidation();
    $actions = Should::notThrow(
        Exception::class, 
        fn() => Action::new(function($a, $b, $c) {}, TypeHint::undefined(), TypeHint::undefined(), TypeHint::undefined()),
        fn() => Action::new(function($a, $b, $c) {}, TypeHint::bool(), TypeHint::undefined(), TypeHint::undefined()),
        fn() => Action::new(function($a, $b, $c) {}, TypeHint::bool(), TypeHint::union("?float"), TypeHint::undefined()),
        fn() => Action::new(function($a, $b, $c) {}, TypeHint::bool(), TypeHint::union("?float"), TypeHint::string()),
    );
    Should::notThrow(Exception::class, fn() => ($actions[0])(1, 2, 3), fn() => ($actions[1])(1, 2, 3), fn() => ($actions[2])(1, 2,3));
    Should::notThrow(Exception::class, fn() => ($actions[0])("str", 1.0, true), fn() => ($actions[1])("str", null, true), fn() => ($actions[2])("str", 1.0, true));


    Action::enableValidation();
    Should::notThrow(
        Exception::class, 
        fn() => Action::new(function($a, $b, $c) {}, TypeHint::bool(), TypeHint::undefined(), TypeHint::undefined()),
        fn() => Action::new(function($a, $b, $c) {}, TypeHint::bool(), TypeHint::union("?float"), TypeHint::undefined()),
        fn() => Action::new(function($a, $b, $c) {}, TypeHint::bool(), TypeHint::union("?float"), TypeHint::string()),
    );
    $actions = Should::notThrow(
        Exception::class, 
        fn() => Action::new(function(bool $a, $b, $c) {}, TypeHint::bool(), TypeHint::undefined(), TypeHint::undefined()),
        fn() => Action::new(function(bool $a, ?float $b, $c) {}, TypeHint::bool(), TypeHint::union("?float"), TypeHint::undefined()),
        fn() => Action::new(function(bool $a, ?float $b, string $c) {}, TypeHint::bool(), TypeHint::union("?float"), TypeHint::string()),
    );
    
    Should::notThrow(Exception::class, fn() => ($actions[0])(true, 1.0, "str"), fn() => ($actions[1])(false, null, "str"), fn() => ($actions[2])(true, 1.0, "str"));
    Should::throw(Exception::class, fn() => ($actions[0])("str", 1.0, true), fn() => ($actions[1])("str", null, true), fn() => ($actions[2])("str", 1.0, true));
});