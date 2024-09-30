<?php

declare(strict_types=1);

namespace Pst\Core;

use Pst\Core\Interfaces\IToString;

use ReflectionClass;
use ReflectionProperty;
use InvalidArgumentException;

class ToString {
    private static array $reflectionCache = [];

    // Pure static class
    private function __construct() {}

    /**
     * Converts a value to a string.
     * 
     * @param mixed $value The value to convert to a string.
     * 
     * @return string The string representation of the value.
     */
    public static function toString($value): string {
        $visited = [];
        return self::privateToString($value, $visited);
    }

    public static function objectToString(object $value, bool $showMethods = false): string {
        $visited = [];
        $output = self::privateObjectToString($value, $visited);

        $outputParts = explode("\n", trim($output));

        $output = implode("\n    ", $outputParts);

        $output = $output[-1] !== "}" ? $output : rtrim(substr($output, 0, -1)) . "\n}";

        // replace { followed by any whitespaces and then } with just {}
        $output = preg_replace("/\{\s+\}/", "{}", $output);

        return $output;
    }

    /**
     * Gets/Sets a cached ReflectionClass instance for a class name.
     * 
     * @param string $className The name of the class to get the ReflectionClass instance for.
     * 
     * @return ReflectionClass The ReflectionClass instance for the class.
     */
    private static function getReflectionClass(string $className): ReflectionClass {
        return self::$reflectionCache[$className] ??= new ReflectionClass($className);
    }

    /**
     * Removes the namespace from a class name.
     * 
     * @param string $className The class name to remove the namespace from.
     * 
     * @return string The class name without the namespace.
     */
    private static function removeNamespace(string $className): string {
        if (strpos($className, "\\") !== false) {
            return substr($className, strrpos($className, "\\") + 1);
        }

        return $className;
    }

    /**
     * Converts a type value to a keyword.
     * 
     * @param string $getTypeValue The type value to convert to a keyword.
     * 
     * @return string The keyword representation of the type value.
     */
    private static function getTypeValueToKeyword(string $getTypeValue): string {
        if ($getTypeValue == "NULL") {
            return "null";
        } else if ($getTypeValue == "boolean") {
            return "bool";
        } else if ($getTypeValue == "double") {
            return "float";
        }

        return $getTypeValue;
    }

    /**
     * Converts a value to a string.
     * 
     * @param mixed $value The value to convert to a string.
     * @param array $visited The visited objects.
     * 
     * @return string The string representation of the value.
     */
    private static function privateToString($value, array &$visited): string {
        if (is_string($value)) {
            return '"' . $value . '"';
        } else if (is_float($value)) {
            return number_format($value, 2);
        } else if (is_int($value)) {
            return (string)$value;
        } else if (is_bool($value)) {
            return $value ? "true" : "false";
        } else if (is_null($value)) {
            return "null";
        } else if (is_array($value)) {
            return self::arrayToString($value, $visited);
        } else if (is_object($value)) {
            $privateObjectToString = self::privateObjectToString($value, $visited);

            $privateObjectToStringParts = explode("\n", trim($privateObjectToString));

            $output = implode("\n    ", $privateObjectToStringParts);

            $output = $output[-1] !== "}" ? $output : rtrim(substr($output, 0, -1)) . "\n}";

            // replace { followed by any whitespaces and then } with just {}
            $output = preg_replace("/\{\s+\}/", "{}", $output);

            return $output;
        } else {
            throw new InvalidArgumentException("Unsupported type: " . gettype($value));
        }
    }

    /**
     * Converts an array to a string.
     * 
     * @param array $array The array to convert to a string.
     * @param array $visited The visited objects.
     * 
     * @return string The string representation of the array.
     */
    private static function arrayToString(array $array, array &$visited): string {
        if (count($array) === 0) {
            return "[]";
        }

        $i = 0;

        $arrayValues = array_reduce(array_keys($array), function ($carry, $key) use (&$i, $array, &$visited) {
            $itemValue = $array[$key];

            if ($i === $key) {
                $carry[$key] = self::privateToString($itemValue, $visited);
            } else {
                $carry[$key] = "$key => " . self::privateToString($itemValue, $visited);                
            }

            $i++;

            return $carry;
        }, []);

        return "[" . implode(",", $arrayValues) . "]";
    }

    /**
     * Converts the properties of an object to a string.
     * 
     * @param object $object The object to convert the properties of to a string.
     * @param int $type The type of properties to convert.
     * @param array $visited The visited objects.
     * 
     * @return array The string representation of the properties of the object.
     */
    private static function propertiesToString(object $object, int $type, array &$visited): array {
        $className = get_class($object);
        $reflectionClass = self::getReflectionClass($className);

        $properties = $reflectionClass->getProperties($type);

        return array_reduce($properties, function($carry, ReflectionProperty $item) use ($object, &$visited) {
            $item->setAccessible(true);
            $itemValue = $item->getValue($object);

            $itemType = "mixed";

            if ($item->hasType()) {
                if (method_exists($item, "getTypes")) {
                    $itemType = implode("|", call_user_func([$item, "getTypes"]));
                    
                } else {
                    if ($item->getType()->allowsNull()) {
                        $itemType = "null|" . self::removeNamespace($item->getType()->getName());
                    } else {
                        $itemType = self::removeNamespace($item->getType()->getName());
                    }
                }
            }
            
            $itemName = $item->getName();

            $itemName = self::removeNamespace($itemName);


            $carry[$itemType . " \$" . $itemName] = self::privateToString($itemValue, $visited);
            return $carry;
        }, []);
    }

    /**
     * Converts an object to a string.
     * 
     * @param object $object The object to convert to a string.
     * @param array $visited The visited objects.
     * 
     * @return string The string representation of the object.
     */
    private static function privateObjectToString(object $object, array &$visited): string {
        $hash = spl_object_hash($object);

        if (isset($visited[$hash])) {
            return self::removeNamespace(get_class($object)) . " (recursive)";
        }

        $visited[$hash] = true;

        if ($object instanceof IToString) {
            return $object->toString();
        }

        $className = get_class($object);
        $reflectionClass = self::getReflectionClass($className);
        $extends = "";
        $implements = "";

        if (($parent = $reflectionClass->getParentClass()) !== false) {
            $parentClass = $parent->getName();

            $extends = "extends " . self::removeNamespace($parentClass);
        }

        if (!empty($interfaces = $reflectionClass->getInterfaceNames())) {
            $interfaces = array_map(function($interface) {
                return static::removeNamespace($interface);
                
            }, $interfaces);
            $implements = "implements " . implode(", ", $interfaces);
        }


        $output = "class " . self::removeNamespace($className)/* . " " . $extends . " " . $implements*/ . " {\n";

        if (!empty($privateProperties = self::propertiesToString($object, ReflectionProperty::IS_PRIVATE, $visited))) {
            foreach ($privateProperties as $key => $value) {
                $output .= "private $key = $value\n";
            }
            $output .= "\n";
        }

        if (!empty($protectedProperties = self::propertiesToString($object, ReflectionProperty::IS_PROTECTED, $visited))) {
            foreach ($protectedProperties as $key => $value) {
                $output .= "protected $key = $value\n";
            }
            $output .= "\n";
        }

        if (!empty($publicProperties = self::propertiesToString($object, ReflectionProperty::IS_PUBLIC, $visited))) {
            foreach ($publicProperties as $key => $value) {
                $output .= "public $key = $value\n";
            }
            $output .= "\n";
        }

        // remove the last character from output
        $output = rtrim($output) . "\n";
        
        $output .= "}\n";

        return $output;
    }
}