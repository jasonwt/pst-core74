<?php

/*TDD*/

declare(strict_types=1);

namespace ActivatorTests;

use Pst\Core\Types\Type;

use Pst\Core\Activator;

use Pst\Testing\Should;

require_once(__DIR__ . "/../vendor/autoload.php");

Should::executeTests(function() {
    Should::equal(gettype($nullValue = Activator::createInstance($nullType = Type::null())), "NULL");
    Should::equal($nullType->defaultValue(), $nullValue, null);

    Should::equal(gettype($intValue = Activator::createInstance($intType = Type::typeOf(1))), "integer");
    Should::equal($intType->defaultValue(), $intValue, 0);

    Should::equal(gettype($floatValue = Activator::createInstance($floatType = Type::typeOf(1.0))), "double");
    Should::equal($floatType->defaultValue(), $floatValue, 0.0);

    Should::equal(gettype($boolValue = Activator::createInstance($boolType = Type::typeOf(true))), "boolean");
    Should::equal($boolType->defaultValue(), $boolValue, false);

    Should::equal(gettype($stringValue = Activator::createInstance($stringType = Type::typeOf("string"))), "string");
    Should::equal($stringType->defaultValue(), $stringValue, "");

    Should::equal(gettype($arrayValue = Activator::createInstance($arrayType = Type::typeOf([]))), "array");
    Should::equal($arrayType->defaultValue(), $arrayValue, []);
    
});