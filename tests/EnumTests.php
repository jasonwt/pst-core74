<?php

/*TDD*/

declare(strict_types=1);

namespace EnumTests;

use Pst\Core\Enum;

use Pst\Testing\Should;

require_once(__DIR__ . "/../vendor/autoload.php");

Should::executeTests(function() {
    class ColorEnum extends Enum {
        public static function cases(): array {
            return ["Red" => "Red", "Green" => "Green", "Blue" => "Blue"];
        }

        public static function Red(): ColorEnum {
            return new ColorEnum("Red");
        }

        public static function Green(): ColorEnum {
            return new ColorEnum("Green");
        }

        public static function Blue(): ColorEnum {
            return new ColorEnum("Blue");
        }
    }

    Should::equal("Red", (string) ColorEnum::Red());
    Should::beTrue(ColorEnum::Red() == ColorEnum::Red());
    Should::beFalse(ColorEnum::Red() == ColorEnum::Green());
});