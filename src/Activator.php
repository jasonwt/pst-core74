<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;

use Pst\Core\Types\Type;

use Closure;
use ReflectionClass;

use InvalidArgumentException;

/**
 * Provides a mechanism for creating instances of types.
 * 
 * @package PST\Core
 * 
 * @version 1.0.0
 * 
 * @since 1.0.0
 * 
 * @see Type
 */
final class Activator {
    private function __construct() {}

    /**
     * Determines whether the specified type can be created.
     * 
     * @param string|Type $type - The type to create.
     * 
     * @return bool - True if the type can be created; otherwise, false.
     * 
     * @throws InvalidArgumentException
     * 
     */
    public static function canCreateInstance(/*string|Type*/$type): bool {
        if ($type === null) {
            throw new InvalidArgumentException("Type cannot be null.");
        } else if (is_string($type)) {
            $type = Type::typeOf(trim($type));
        }

        if (!$type instanceof Type) {
            throw new InvalidArgumentException("Type '" . (string) $type . "' parameter is not a valid type.");
        }

        if ($type->isValueType()) {
            return true;
        } else if (!$type->isClass() || $type->isAbstract()) {
            return false;
        }

        $typeName = $type->name();

        $reflectionClass = new ReflectionClass($typeName);

        if (!$reflectionClass->isInstantiable()) {
            return false;
        } else if (($classConstructor = $reflectionClass->getConstructor()) === null) {
            return true;
        } else if (empty($classConstructorParameters = $classConstructor->getParameters())) {
            return true;
        }

        return false;
    }

    /**
     * Creates a new instance factory for the specified type.
     * 
     * @param string|Type $type - The type to create.
     * 
     * @return null|Closure - The created instance factory or null on failure.
     * 
     * @throws InvalidArgumentException
     */
    public static function createInstanceFactory(/*string|Type*/$type): ?Closure {
        if ($type === null) {
            throw new InvalidArgumentException("Type cannot be null.");
        } else if (is_string($type)) {
            $type = Type::typeOf(trim($type));
        }

        if (!$type instanceof Type) {
            throw new InvalidArgumentException("Type '" . (string) $type . "' parameter is not a valid type.");
        }

        if ($type->isValueType() || $type->fullName() === "null") {
            return function () use ($type) {
                return $type->defaultValue();
            };
        } else if (!$type->isClass() || $type->isAbstract()) {
            return null;
        }

        $typeName = $type->name();

        $reflectionClass = new ReflectionClass($typeName);

        if (!$reflectionClass->isInstantiable()) {
            return null;
        } else if (($classConstructor = $reflectionClass->getConstructor()) === null) {
            return function () use ($reflectionClass) {
                return $reflectionClass->newInstance();
            };
        } else if (empty($classConstructorParameters = $classConstructor->getParameters())) {
            return function () use ($reflectionClass) {
                return $reflectionClass->newInstance();
            };
        }

        return null;
    }

    /**
     * Creates a new instance of the specified type.
     * 
     * @param string|Type $type - The type to create.
     * 
     * @return mixed - The created instance or null on failure.
     * 
     * @throws InvalidArgumentException
     */
    public static function createInstance(/*string|Type*/$type) {
        return self::createInstanceFactory($type)();
    }
}