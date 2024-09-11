<?php

/*TDD*/

declare(strict_types=1);

namespace TypeTests;

require_once(__DIR__ . "/../vendor/autoload.php");

use InvalidArgumentException;
use Exception;
use Pst\Core\Types\ITypeHint;
use Pst\Core\Types\Type;
use Pst\Core\Types\TypeHint;
use Pst\Testing\Should;

trait Trait1 {};
trait Trait2 {use Trait1; };

interface Interface1 {}
interface Interface2 extends Interface1 {}
abstract class AbstractClass implements Interface2 {}
class TestClass extends AbstractClass implements Interface2 {}

function isAssignableToTest(ITypeHint $toType, ITypeHint $fromType) {
    $toTypeName = $toType->fullName();
    $fromTypeName = $fromType->fullName();

    $toTypeParts = explode("|", $toTypeName);
    $fromTypeParts = explode("|", $fromTypeName);

    foreach ($fromTypeParts as $fromTypePart) {
        $fromTypeIsHint = isset(TypeHint::TYPES[$fromTypePart]);

        foreach ($toTypeParts as $toTypePart) {
            if ($fromTypePart == $toTypePart) {
                continue 2;
            } else if ($fromTypePart === "void" && $toTypePart === "void") {
                continue 2;
            } else if ($fromTypePart === "void" || $toTypePart === "void") {
                continue;
            } if ($toTypePart === "mixed" || $toTypePart === "undefined") {
                continue 2;
            } else if ($fromTypePart === "mixed" || $fromTypePart === "undefined") {
                continue;
            }

            $toTypeIsHint = isset(TypeHint::TYPES[$toTypePart]);
            
            if (!$fromTypeIsHint && !$toTypeIsHint) {
                if (is_a($fromTypePart, $toTypePart, true)) {
                    continue 2;
                }

                continue;
            }

            if ($toTypeIsHint) {
                $fromType = Type::fromTypeName($fromTypePart);

                if ($toTypePart === "object" && $fromType->isObject()) {
                    continue 2;
                } else if ($toTypePart === "enum" && $fromType->isEnum()) {
                    continue 2;
                } else if ($toTypePart === "trait" && $fromType->isTrait()) {
                    continue 2;
                // } else if ($toTypePart === "resource" && $fromType->isResource()) {
                }
            }
        }

        return false;
    }

    return true;
}

Should::executeTests(function() {
    $types = [
        Type::array(), Type::bool(), Type::float(), Type::int(), Type::null(), Type::string(), Type::void(), 
        Type::interface(Interface1::class), Type::interface(Interface2::class), 
        Type::trait(Trait1::class), Type::trait(Trait2::class), 
        Type::class(AbstractClass::class), Type::class(TestClass::class),


        TypeHint::array(), TypeHint::bool(), TypeHint::float(), TypeHint::int(), TypeHint::null(), TypeHint::string(), TypeHint::void(), TypeHint::object(),
        TypeHint::fromTypeNames("int|null"), TypeHint::fromTypeNames("null|int"), TypeHint::fromTypeNames("?int")
    ];

    foreach ($types as $type) {
        Should::equal((string) $type, $type->fullName());
    }

    $expectedResults = [

    ];

    foreach ($types as $toType) {
        foreach ($types as $fromType) {
            //$expectedResults = $expectedResults[$toType->fullName()][$fromType->fullName()] ?? isAssignableToTest($toType, $fromType);
            $expectedResults = isAssignableToTest($toType, $fromType);

//            echo "Testing {$toType}->isAssignableFrom({$fromType}): {$expectedResults}\n";
            Should::equal($expectedResults, $toType->isAssignableFrom($fromType));
        }
    }

//     Should::equal((string) ($arrayType = Type::array()), "array", $arrayType->fullName());
//     Should::equal((string) ($boolType = Type::bool()), "bool", $boolType->fullName());
//     Should::equal((string) ($floatType = Type::float()), "float", $floatType->fullName());
//     Should::equal((string) ($intType = Type::int()), "int", $intType->fullName());
//     Should::equal((string) ($nullType = Type::null()), "null", $nullType->fullName());
//     Should::equal((string) ($stringType = Type::string()), "string", $stringType->fullName());
//     Should::equal((string) ($voidType = Type::void()), "void", $voidType->fullName());

//     Should::equal((string) ($nullIntType = TypeHint::fromTypeNames("?int")), "int|null", $nullIntType->fullName());
//     Should::equal((string) ($nullIntFloatType = TypeHint::fromTypeNames("?int|float")), "float|int|null", $nullIntFloatType->fullName());
//     Should::equal((string) ($nullArrayIntFloat = TypeHint::fromTypeNames("?array", "int", "float")), "array|float|int|null", $nullArrayIntFloat->fullName());

//     Should::equal((string) ($trait1Type = Type::typeOf(Trait1::class)), Trait1::class, $trait1Type->fullName());
//     Should::beTrue($trait1Type->isAbstract(), $trait1Type->isTrait());
//     Should::equal((string) ($trait2Type = Type::typeOf(Trait2::class)), Trait2::class, $trait2Type->fullName());
//     Should::beTrue($trait2Type->isAbstract(), $trait2Type->isTrait());
//     Should::equal((string) ($interface1Type = Type::typeOf(Interface1::class)), Interface1::class, $interface1Type->fullName());
//     Should::beTrue($interface1Type->isAbstract(), $interface1Type->isInterface(), $interface1Type->isObject());
//     Should::equal((string) ($interface2Type = Type::typeOf(Interface2::class)), Interface2::class, $interface2Type->fullName());
//     Should::beTrue($interface2Type->isAbstract(), $interface2Type->isInterface(), $interface2Type->isObject());
//     Should::equal((string) ($abstractClassType = Type::typeOf(AbstractClass::class)), AbstractClass::class, $abstractClassType->fullName());
//     Should::beTrue($abstractClassType->isAbstract(), $abstractClassType->isClass(), $abstractClassType->isObject());
//     Should::equal((string) ($testClassType = Type::typeOf(TestClass::class)), TestClass::class, $testClassType->fullName());
//     Should::beTrue(!$testClassType->isAbstract(), $testClassType->isClass(), $testClassType->isObject());

//     Should::throw(InvalidArgumentException::class, fn() => TypeHint::fromTypeNames("?mixed"));
//     Should::throw(InvalidArgumentException::class, fn() => TypeHint::fromTypeNames("?undefined"));
//     Should::throw(InvalidArgumentException::class, fn() => TypeHint::fromTypeNames("?void"));
//     Should::notThrow(Exception::class, fn() => TypeHint::fromTypeNames("?object"));

//     $isAssignableTestParameters = [
//         // "array" => [
//         //     "to"   => [
//         //         "array" => true, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => true, "undefined" => true, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ],
//         //     "from" => [
//         //         "array" => true, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => false, "undefined" => false, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ]
//         // ],
//         // "bool" => [
//         //     "to"   => [
//         //         "array" => false, "bool" => true, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => true, "undefined" => true, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ],
//         //     "from" => [
//         //         "array" => false, "bool" => true, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => false, "undefined" => false, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ]
//         // ],
//         // "float" => [
//         //     "to"   => [
//         //         "array" => false, "bool" => false, "float" => true, "int" => false, "null" => false, "string" => false, "mixed" => true, "undefined" => true, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => true
//         //     ],
//         //     "from" => [
//         //         "array" => false, "bool" => false, "float" => true, "int" => false, "null" => false, "string" => false, "mixed" => false, "undefined" => false, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ]
//         // ],
//         // "int" => [
//         //     "to"   => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => true, "null" => false, "string" => false, "mixed" => true, "undefined" => true, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => true, "null|int|float" => true
//         //     ],
//         //     "from" => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => true, "null" => false, "string" => false, "mixed" => false, "undefined" => false, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ]
//         // ],
//         // "null" => [
//         //     "to"   => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => true, "string" => false, "mixed" => true, "undefined" => true, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => true, "null|int|float" => true
//         //     ],
//         //     "from" => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => true, "string" => false, "mixed" => false, "undefined" => false, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ]
//         // ],
//         // "string" => [
//         //     "to"   => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => true, "mixed" => true, "undefined" => true, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ],
//         //     "from" => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => true, "mixed" => false, "undefined" => false, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ]
//         // ],
//         // "void" => [
//         //     "to"   => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => false, "undefined" => false, "object" => false, "void" => true,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ],
//         //     "from" => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => false, "undefined" => false, "object" => false, "void" => true,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ]
//         // ],

//         // "mixed" => [
//         //     "to"   => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => true, "undefined" => true, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ],
//         //     "from" => [
//         //         "array" => true, "bool" => true, "float" => true, "int" => true, "null" => true, "string" => true, "mixed" => true, "undefined" => true, "object" => true, "void" => false,
//         //         Trait1::class => true, Trait2::class => true, Interface1::class => true, Interface2::class => true, AbstractClass::class => true, TestClass::class => true,
//         //         "null|int" => true, "null|int|float" => true
//         //     ]
//         // ],
//         // "undefined" => [
//         //     "to"   => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => true, "undefined" => true, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ],
//         //     "from" => [
//         //         "array" => true, "bool" => true, "float" => true, "int" => true, "null" => true, "string" => true, "mixed" => true, "undefined" => true, "object" => true, "void" => false,
//         //         Trait1::class => true, Trait2::class => true, Interface1::class => true, Interface2::class => true, AbstractClass::class => true, TestClass::class => true,
//         //         "null|int" => true, "null|int|float" => true
//         //     ]
//         // ],
//         // "object" => [
//         //     "to"   => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => true, "undefined" => true, "object" => true, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => true /*false*/, Interface2::class => true /*false*/, AbstractClass::class => true /*false*/, TestClass::class => true /*false*/,
//         //         "null|int" => false, "null|int|float" => false
//         //     ],
//         //     "from" => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => false, "undefined" => false, "object" => true, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => true, Interface2::class => true, AbstractClass::class => true, TestClass::class => true,
//         //         "null|int" => false, "null|int|float" => false
//         //     ]
//         // ],

//         // Trait1::class => [
//         //     "to"   => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => true, "undefined" => true, "object" => false, "void" => false,
//         //         Trait1::class => true, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ],
//         //     "from" => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => false, "undefined" => false, "object" => false, "void" => false,
//         //         Trait1::class => true, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ]
//         // ],
//         // Trait2::class => [
//         //     "to"   => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => true, "undefined" => true, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => true, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ],
//         //     "from" => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => false, "undefined" => false, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => true, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ]
//         // ],

//         // Interface1::class => [
//         //     "to"   => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => true, "undefined" => true, "object" => true, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => true, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ],
//         //     "from" => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => false, "undefined" => false, "object" => true /*false*/, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => true, Interface2::class => true, AbstractClass::class => true, TestClass::class => true,
//         //         "null|int" => false, "null|int|float" => false
//         //     ]
//         // ],
//         // Interface2::class => [
//         //     "to"   => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => true, "undefined" => true, "object" => true, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => true, Interface2::class => true, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ],
//         //     "from" => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => false, "undefined" => false, "object" => true /*false*/, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => true, AbstractClass::class => true, TestClass::class => true,
//         //         "null|int" => false, "null|int|float" => false
//         //     ]
//         // ],

//         // AbstractClass::class => [
//         //     "to"   => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => true, "undefined" => true, "object" => true, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => true, Interface2::class => true, AbstractClass::class => true, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => false
//         //     ],
//         //     "from" => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => false, "undefined" => false, "object" => true /*false*/, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => true, TestClass::class => true,
//         //         "null|int" => false, "null|int|float" => false
//         //     ]
//         // ],
//         // TestClass::class => [
//         //     "to"   => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => true, "undefined" => true, "object" => true, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => true, Interface2::class => true, AbstractClass::class => true, TestClass::class => true,
//         //         "null|int" => false, "null|int|float" => false
//         //     ],
//         //     "from" => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => false, "undefined" => false, "object" => true /*false*/, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => true,
//         //         "null|int" => false, "null|int|float" => false
//         //     ]
//         // ],

        

//         // "null|int" => [
//         //     "to"   => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => true, "undefined" => true, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => true, "null|int|float" => true
//         //     ],
//         //     "from" => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => true, "null" => true, "string" => false, "mixed" => false, "undefined" => false, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => true, "null|int|float" => false
//         //     ]
//         // ],
//         // "null|int|float" => [
//         //     "to"   => [
//         //         "array" => false, "bool" => false, "float" => false, "int" => false, "null" => false, "string" => false, "mixed" => true, "undefined" => true, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => false, "null|int|float" => true
//         //     ],
//         //     "from" => [
//         //         "array" => false, "bool" => false, "float" => true, "int" => true, "null" => true, "string" => false, "mixed" => false, "undefined" => false, "object" => false, "void" => false,
//         //         Trait1::class => false, Trait2::class => false, Interface1::class => false, Interface2::class => false, AbstractClass::class => false, TestClass::class => false,
//         //         "null|int" => true, "null|int|float" => true
//         //     ]
//         // ]


//     ];

//     foreach ($isAssignableTestParameters as $type => $tests) {
// //        echo "Testing $type\n";
//         foreach ($tests["from"] as $otherType => $expected) {
// //            echo "\tTesting $type from $otherType\n";
//             Should::equal($expected, TypeHint::fromTypeNames($type)->isAssignableFrom(TypeHint::fromTypeNames($otherType)));
//         }

//         foreach ($tests["to"] as $otherType => $expected) {
// //            echo "\tTesting $type to $otherType\n";
//             Should::equal($expected, TypeHint::fromTypeNames($type)->isAssignableTo(TypeHint::fromTypeNames($otherType)));
//         }
//     }

});