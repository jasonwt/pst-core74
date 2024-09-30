<?php

declare(strict_types=1);

namespace Pst\Core\Runtime;

use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Types\ITypeHint;
use Pst\Core\CoreObject;

use Pst\Core\Traits\PropertiesArrayTrait;

use Closure;

final class Settings {
    private static ?object $instance = null;

    private static function instance(): object {
        return self::$instance ??= new class() extends CoreObject {
            use PropertiesArrayTrait {
                propertiesIterator as public getIterator;
                addProperty as public;
                propertyExists as public;
                getPropertyNames as public;
                getProperty as public;
                getProperties as public;
                getPropertyValue as public;
                getPropertyValues as public;
                setPropertyValue as public;
                resetPropertyValue as public;
            }

            public function T(): ITypeHint {
                return TypeHintFactory::tryParseTypeName("mixed");
            }
        };
    }

    public static function registerSetting(string $name, $defaultValue = null, ?ITypeHint $typeHint = null, ?Closure $validationClosure = null): void {
        self::instance()->addProperty($name, $defaultValue, $typeHint, $validationClosure);
    }

    public static function tryRegisterSetting(string $name, $defaultValue = null, ?ITypeHint $typeHint = null, ?Closure $validationClosure = null): bool {
        if (self::instance()->propertyExists($name)) {
            return false;
        }

        self::instance()->addProperty($name, $defaultValue, $typeHint, $validationClosure);

        return true;
    }

    public static function settingExists(string $name): bool {
        return self::instance()->propertyExists($name);
    }

    public static function getSetting(string $name) {
        return self::instance()->getPropertyValue($name);
    }

    public static function setSetting(string $name, $value): void {
        self::instance()->setPropertyValue($name, $value);
    }
}