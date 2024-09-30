<?php

/*TD/D*/

declare(strict_types=1);

namespace ActionTests;

use Pst\Core\Types\TypeHintFactory;

use Pst\Core\Action;

use Pst\Testing\Should;

use Exception;
use InvalidArgumentException;

require_once(__DIR__ . "/../vendor/autoload.php");

Should::executeTests(function() {
    // echo "need to look at a section in ActionTests.php\n";
    // // Should::throw(
    // //     InvalidArgumentException::class, 
    // //     fn() => Action::new(function($a, $b, $c) {}, TypeHintFactory::undefined(), TypeHintFactory::undefined()),
    // //     fn() => Action::new(function($a, $b, $c) {}, TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined()),
    // //     fn() => Action::new(function($a, $b, $c): bool { return true;}, TypeHintFactory::undefined(), TypeHintFactory::undefined()),
    // //     fn() => Action::new(function($a, $b, $c): bool { return true;}, TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined()),
    // //     // TODO: below should throw because i think to many parameters.  I think it is not catching it because of the new void 
    // //     // to represent optional argument. will need to look into this
    // //     // fn() => Action::new(function($a, $b, $c): bool { return true;}, TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined()),
    // // );

    // //Action::disableValidation();
    // $actions = Should::notThrow(
    //     Exception::class, 
    //     fn() => Action::new(function($a, $b, $c) {}, TypeHintFactory::undefined(), TypeHintFactory::undefined(), TypeHintFactory::undefined()),
    //     fn() => Action::new(function($a, $b, $c) {}, TypeHintFactory::bool(), TypeHintFactory::undefined(), TypeHintFactory::undefined()),
    //     fn() => Action::new(function($a, $b, $c) {}, TypeHintFactory::bool(), TypeHintFactory::union("?float"), TypeHintFactory::undefined()),
    //     fn() => Action::new(function($a, $b, $c) {}, TypeHintFactory::bool(), TypeHintFactory::union("?float"), TypeHintFactory::string()),
    // );
    // Should::notThrow(Exception::class, fn() => ($actions[0])(1, 2, 3), fn() => ($actions[1])(1, 2, 3), fn() => ($actions[2])(1, 2,3));
    // Should::notThrow(Exception::class, fn() => ($actions[0])("str", 1.0, true), fn() => ($actions[1])("str", null, true), fn() => ($actions[2])("str", 1.0, true));


    // //Action::enableValidation();
    // Should::notThrow(
    //     Exception::class, 
    //     fn() => Action::new(function($a, $b, $c) {}, TypeHintFactory::bool(), TypeHintFactory::undefined(), TypeHintFactory::undefined()),
    //     fn() => Action::new(function($a, $b, $c) {}, TypeHintFactory::bool(), TypeHintFactory::union("?float"), TypeHintFactory::undefined()),
    //     fn() => Action::new(function($a, $b, $c) {}, TypeHintFactory::bool(), TypeHintFactory::union("?float"), TypeHintFactory::string()),
    // );
    // $actions = Should::notThrow(
    //     Exception::class, 
    //     fn() => Action::new(function(bool $a, $b, $c) {}, TypeHintFactory::bool(), TypeHintFactory::undefined(), TypeHintFactory::undefined()),
    //     fn() => Action::new(function(bool $a, ?float $b, $c) {}, TypeHintFactory::bool(), TypeHintFactory::union("?float"), TypeHintFactory::undefined()),
    //     fn() => Action::new(function(bool $a, ?float $b, string $c) {}, TypeHintFactory::bool(), TypeHintFactory::union("?float"), TypeHintFactory::string()),
    // );

    
    
    // Should::notThrow(Exception::class, fn() => ($actions[0])(true, 1.0, "str"), fn() => ($actions[1])(false, null, "str"), fn() => ($actions[2])(true, 1.0, "str"));
    // Should::throw(Exception::class, fn() => ($actions[0])("str", 1.0, true), fn() => ($actions[1])("str", null, true), fn() => ($actions[2])("str", 1.0, true));
});