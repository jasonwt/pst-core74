<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;

use Pst\Core\Types\Type;

use Pst\Core\Exceptions\InvalidCastException;

/**
 * Provides methods for converting values between different types.
 * 
 * @since 1.0.0
 * 
 * @package PST\Core
 * 
 * @version 1.0.0
 * 
 * @since 1.0.0
 * 
 * @final
 * 
 * @see IConvertible
 * 
 */
final class Convert {
    private function __construct() {}

    /**
     * Converts the specified value to a boolean value.
     * 
     * @param $value The value to convert.
     * 
     * @return bool The boolean value of the specified value.
     * 
     * @throws InvalidCastException The value cannot be converted to a boolean value.
     * 
     */
    public static function toBoolean($value): bool {
        $valueType = Type::typeOf($value);

        if ($valueType->isValueType() || $valueType->fullName() === "null") {
            return (bool) $value;
        }

        if ($value instanceof IConvertible) {
            return $value->toBoolean();
        }

        throw new InvalidCastException();
    }

    /**
     * Converts the specified value to an integer value.
     * 
     * @param $value The value to convert.
     * 
     * @return int The integer value of the specified value.
     * 
     * @throws InvalidCastException The value cannot be converted to an integer value.
     * 
     */
    public static function toInteger($value): int {
        $valueType = Type::typeOf($value);

        if ($valueType->isValueType()  || $valueType->fullName() === "null") {
            return (int) $value;
        }

        if ($value instanceof IConvertible) {
            return $value->toInteger();
        }

        throw new InvalidCastException();
    }

    /**
     * Converts the specified value to a float value.
     * 
     * @param $value The value to convert.
     * 
     * @return float The float value of the specified value.
     * 
     * @throws InvalidCastException The value cannot be converted to a float value.
     * 
     */
    public static function toFloat($value): float {
        $valueType = Type::typeOf($value);

        if ($valueType->isValueType()  || $valueType->fullName() === "null") {
            return (float) $value;
        }

        if ($value instanceof IConvertible) {
            return $value->toFloat();
        }

        throw new InvalidCastException();
    }

    /**
     * Converts the specified value to a string value.
     * 
     * @param $value The value to convert.
     * 
     * @return string The string value of the specified value.
     * 
     * @throws InvalidCastException The value cannot be converted to a string value.
     * 
     */
    public static function toString($value): string {
        $valueType = Type::typeOf($value);

        if ($valueType->isValueType()  || $valueType->fullName() === "null") {
            return (string) $value;
        }

        if ($value instanceof IConvertible) {
            return $value->toString();
        }

        throw new InvalidCastException();
    }

    public static function tryChangeType($value, Type $conversionType) {
        $valueType = Type::typeOf($value);

        if ($value instanceof IConvertible) {
            try {
                return $value->toType($conversionType);
            } catch (InvalidCastException $e) {
                return null;
            }
        }
        
        if ($valueType->isValueType() || $valueType->fullName() === "null") {
            if ($conversionType->isValueType() || $conversionType->fullName() === "null") {
                switch ((string) $conversionType) {
                    case 'bool':
                        return (bool) $value;
                    case 'int':
                        return (int) $value;
                    case 'float':
                        return (float) $value;
                    case 'string':
                        return (string) $value;
                }
            }
        }

        return null;
    }

    /**
     * Converts the specified value to the specified type.
     * 
     * @param $value The value to convert.
     * @param Type $conversionType The type to convert the value to.
     * 
     * @return mixed The value converted to the specified type.
     * 
     * @throws InvalidCastException The value cannot be converted to the specified type.
     * 
     */
    public static function changeType($value, Type $conversionType) {
        if (($convertedValue = self::tryChangeType($value, $conversionType)) !== null) {
            return $convertedValue;
        }

        throw new InvalidCastException();
    }
}