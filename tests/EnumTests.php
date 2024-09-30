<?php

/*TDD*/

declare(strict_types=1);

namespace EnumTests;

use Pst\Core\Enum;
use Pst\Core\Interfaces\IEnum;

use Pst\Testing\Should;

require_once(__DIR__ . "/../vendor/autoload.php");

class ColorEnum extends Enum implements IEnum {

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

Should::executeTests(function() {
    // echo "\n";

    // // print_r(ColorEnum::cases());
    // // print_r((string) ColorEnum::Red());

    // $colorEnumRed = ColorEnum::Red();
    // echo "colorEnumRed->className(): " . $colorEnumRed->ClassName() . "\n";
    // echo "colorEnumRed::className(): " . $colorEnumRed::ClassName() . "\n";
    // echo "(string) colorEnumRed: " . (string) $colorEnumRed . "\n";
    // echo "ColorEnum::class: " . ColorEnum::class . "\n";

    // print_r($colorEnumRed);

    // // echo "ColorEnum::cases(): " . print_r(ColorEnum::cases(), true) . "\n";
    // // echo "ColorEnum::Red(): " . print_r(ColorEnum::Red(), true) . "\n";

    // // echo "value(): " . ColorEnum::Red()->value() . "\n";
    // // echo "name(): " . ColorEnum::Red()->name() . "\n";
    

    Should::equal("ColorEnum::Red", (string) ColorEnum::Red());
    Should::beTrue(ColorEnum::Red() == ColorEnum::Red());
    Should::beFalse(ColorEnum::Red() == ColorEnum::Green());
});