<?php

/*TDD*/

declare(strict_types=1);

namespace EnumTests;

use Pst\Core\CoreObject;
use Pst\Core\DebugDump\DDO;
use Pst\Core\Enum;
use Pst\Core\Interfaces\IEnum;
use Pst\Core\Traits\EnumTrait;
use Pst\Testing\Should;

use function Pst\Core\dd;

require_once(__DIR__ . "/../vendor/autoload.php");

class TestEnum  {
    private string $privateString = "";
    protected int $protectedInt = 0;
    public float $publicFloat = 0.0;

    private static int $privateStaticInt = 0;
    protected static string $protectedStaticString = "";
    public static bool $publicStaticBool = false;

    public static function cases(): array {
        return ["One" => "One", "Two" => "Two", "Three" => "Three"];
    }

    public static function One(): TestEnum {
        return new TestEnum("One");
    }

    public static function Two(): TestEnum {
        return new TestEnum("Two");
    }

    public static function Three(): TestEnum {
        return new TestEnum("Three");
    }

    private function privateMethod(int $int, string $string): bool {
        return true;
    }

    protected function protectedMethod(int $int, string $string): bool {
        return true;
    }

    public function publicMethod(int $int, string $string): bool {
        return true;
    }

    private static function privateStaticMethod(int $int, string $string): bool {
        return true;
    }

    protected static function protectedStaticMethod(int $int, string $string): bool {
        return true;
    }

    public static function publicStaticMethod(int $int, string $string): bool {
        return true;
    }


}

Should::executeTests(function() {

    
    
    
    // echo "\n\n";
    // echo "Class1::intValue = " . Class1::$intValue . "\n";
    // echo "Class1::className() = " . Class1::getClassName() . "\n";
    // echo "\n";
    // echo "Class2::intValue = " . Class2::$intValue . "\n";
    // echo "Class2::className() = " . Class2::getClassName() . "\n";

    // $class1 = new Class1();
    // $class2 = new Class2();

    // echo "\n";
    // echo "Class1::intValue = " . $class1::$intValue . "\n";
    // echo "Class1::className() = " . $class1::getClassName() . "\n";
    // echo "\n";
    // echo "Class2::intValue = " . $class2::$intValue . "\n";
    // echo "Class2::className() = " . $class2::getClassName() . "\n";
    // echo "\n";
    
});