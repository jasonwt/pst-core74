<?php

declare(strict_types=1);

namespace Pst\Core\DebugDump;

use Pst\Core\Interfaces\IToString;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

use InvalidArgumentException;

final class DD {
    private static array $reflectionCache = [];

    private function __construct() {}

    private static function getReflectionClass(string $className): ReflectionClass {
        return self::$reflectionCache[$className] ??= new ReflectionClass($className);
    }


    public static function dump(... $args): string {
        $args = array_reduce($args, function($carry, $item) {
            if ($item instanceof DDO) {
                $carry["options"] = $carry["options"] | $item->options();
            } else {
                $carry["args"][] = $item;
            }
            
            return $carry;
        }, ["options" => 0, "args" => []]);

        if (($options = $args["options"]) === 0) {
            $options = DDO::DEFAULT_OPTIONS()->options();
        }

        $ddOptions = new DDO($options);

        $visited = [];

        $output = array_reduce($args["args"], function($carry, $item) use ($ddOptions, &$visited) {
            $carry .= self::valueToString($item, $ddOptions, $visited) . "\n";
            return $carry;
        }, "") . "\n";

        if ($ddOptions->isOptionSet(DDO::SHOW_BACKTRACE)) {
            $backtrace = array_reduce(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), function($carry, $item) {
                $carry[] = $item["file"] . ":" . $item["line"];
                return $carry;
            }, []);

            $output = rtrim($output) .  "\n\nBacktrace:\n" . implode("\n", $backtrace) . "\n\n";
        }

        if (!$ddOptions->isOptionSet(DDO::NO_STDOUT)) {
            echo $output;
        }
        
        return $output;
    }

    

    private static function classNameToString(string $className, DDO $ddOptions): string {
        if ($ddOptions->isOptionSet(DDO::SHOW_CLASS_NAMESPACE) || strpos($className, "\\") === false) {
            return $className;
        }
        
        return end(explode("\\", $className));
    }

    private static function getTypeValueToKeyword(string $getTypeValue): string {
        if ($getTypeValue == "NULL") {
            $getTypeValue = "null";
        } else if ($getTypeValue == "boolean") {
            $getTypeValue = "bool";
        } else if ($getTypeValue == "integer") {
            $getTypeValue = "int";
        } else if ($getTypeValue == "double") {
            $getTypeValue = "float";
        }

        return $getTypeValue;
    }

    private static function padStringResults(string $results): string {
        $resultsToParts = explode("\n", $results);
        return implode("\n    ", $resultsToParts);
    }

    private static function valueToString($value, DDO $ddOptions, array &$visited): string {
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
            return self::arrayToString($value, $ddOptions, $visited);

        } else if (is_object($value)) {
            return self::objectToString($value, $ddOptions, $visited);

        } else {
            throw new InvalidArgumentException("Unsupported type: " . gettype($value));
        }
    }

    private static function arrayToString(array $array, DDO $ddOptions, array &$visited): string {
        if (count($array) === 0) {
            return "[]";
        }

        $isAssocArray = array_keys($array) !== range(0, count($array) - 1);

        $arrayValues = array_reduce(array_keys($array), function ($carry, $key) use ($isAssocArray, $array, $ddOptions, &$visited) {
            $itemValue = $array[$key];

            if (!$isAssocArray) {
                $carry[$key] = "    " . self::padStringResults(self::valueToString($itemValue, $ddOptions, $visited));
            } else {
                $carry[$key] = "    " . $key . " => " . self::padStringResults(self::valueToString($itemValue, $ddOptions, $visited));
            }

            return $carry;
        }, []);
        

        return "[\n" . implode(",\n", $arrayValues) . "\n]";
    }

    private static function classPropertiesToString(object $object, int $type, bool $staticProperties, DDO $ddOptions, array &$visited): array {
        $className = get_class($object);
        $reflectionClass = self::getReflectionClass($className);

        $properties = $reflectionClass->getProperties($type);

        return array_reduce($properties, function($carry, ReflectionProperty $property) use ($object, $staticProperties, $ddOptions, &$visited) {
            if ($staticProperties !== $property->isStatic()) {
                return $carry;
            }

            $property->setAccessible(true);

            $itemValue = $staticProperties ? $property->getValue(null) : $property->getValue($object);

            $itemType = "mixed";

            if ($property->hasType()) {
                if (method_exists($property, "getTypes")) {
                    $itemType = implode("|", call_user_func([$property, "getTypes"]));
                    
                } else {
                    if ($property->getType()->allowsNull()) {
                        $itemType = "null|" . self::classNameToString($property->getType()->getName(), $ddOptions);
                    } else {
                        $itemType = self::classNameToString($property->getType()->getName(), $ddOptions);
                    }
                }
            }
            
            $itemName = self::classNameToString($property->getName(), $ddOptions);

            $carry[$itemType . " \$" . $itemName] = self::padStringResults(self::valueToString($itemValue, $ddOptions, $visited));
            return $carry;
        }, []);
    }

    

    private static function classMethodsToString(object $object, int $type, bool $staticMethods, DDO $ddOptions, array &$visited): array {
        $className = get_class($object);
        $reflectionClass = self::getReflectionClass($className);

        $methods = $reflectionClass->getMethods($type);

        return array_reduce($methods, function($carry, ReflectionMethod $method) use ($object, $staticMethods, $ddOptions, &$visited) {
            $methodName = $method->getName();

            if ($staticMethods !== $method->isStatic()) {
                return $carry;
            }

            $methodParameters = $method->getParameters();

            $methodParameters = array_reduce($methodParameters, function($carry, $parameter) use ($ddOptions) {
                
                $parameterType = $parameter->hasType() ? self::classNameToString($parameter->getType()->getName(), $ddOptions) : "mixed";

                $parameterName = self::classNameToString($parameter->getName(), $ddOptions);

                $carry[] = "$parameterType \$$parameterName";

                return $carry;
            }, []);

            $methodReturnTypes = $method->hasReturnType() ? self::classNameToString($method->getReturnType()->getName(), $ddOptions) : "mixed";

            $methodParameters = implode(", ", $methodParameters);

            $carry[] = "function $methodName($methodParameters): $methodReturnTypes;";

            return $carry;
        }, []);
    }

    private static function objectToString(object $object, DDO $ddOptions, array &$visited): string {
        $hash = spl_object_hash($object);

        if (isset($visited[$hash])) {
            return $visited[$hash];
        }

        $visited[$hash] = self::classNameToString(get_class($object), $ddOptions) . " (recursive)";

        if ($object instanceof IToString) {
            $visited[$hash] = $object->toString();
            return $visited[$hash];
        } 

        $className = get_class($object);
        $reflectionClass = self::getReflectionClass($className);
        $extends = "";
        $implements = "";

        if ($ddOptions->isOptionSet(DDO::SHOW_CLASS_EXTENDS) && ($parent = $reflectionClass->getParentClass()) !== false) {
            $parentClass = $parent->getName();

            $extends = "    extends " . self::classNameToString($parentClass, $ddOptions);
        }

        if ($ddOptions->isOptionSet(DDO::SHOW_CLASS_IMPLEMENTS) && !empty($interfaces = $reflectionClass->getInterfaceNames())) {
            $interfaces = array_map(function($interface) use ($ddOptions) {
                return "    implements " . self::classNameToString($interface, $ddOptions);
                
            }, $interfaces);

            $implements = implode("\n", $interfaces);
        }

        $output = "class " . self::classNameToString($className, $ddOptions)/* . " " . $extends . " " . $implements*/ . " {";

        $classOutput = "";

        if ($extends !== "") {
            $classOutput .= $extends . "\n\n";
        }

        if ($implements !== "") {
            $classOutput .= $implements . "\n\n";
        }

        $propertiesArray = [
            ReflectionProperty::IS_PRIVATE => "private",
            ReflectionProperty::IS_PROTECTED => "protected",
            ReflectionProperty::IS_PUBLIC => "public"
        ];
        
        foreach ([true => "static", false => ""] as $staticProperties => $staticString) {
            foreach ($propertiesArray as $propertyType => $protectionString) {
                if (!$ddOptions->shouldShowClassProperties($propertyType, (bool) $staticProperties)) {
                    continue;
                }
                
                if (!empty($properties = self::classPropertiesToString($object, $propertyType,(bool) $staticProperties, $ddOptions, $visited))) {
                    $protectionString = $protectionString . rtrim(" " . $staticString) . " ";
                    
                    if ($ddOptions->isOptionSet(DDO::SHOW_COMMENTS)) {
                        $classOutput .= "    /****************************** " . $protectionString . "properties ******************************/\n";
                    }
                    
                    foreach ($properties as $key => $value) {
                        $classOutput .= "    " . $protectionString . "$key = $value\n";
                    }

                    $classOutput .= "\n";
                }
            }
        }

        $methodsArray = [
            ReflectionMethod::IS_PRIVATE => "private",
            ReflectionMethod::IS_PROTECTED => "protected",
            ReflectionMethod::IS_PUBLIC => "public"
        ];

        foreach ([true => "static", false => ""] as $staticMethods => $staticString) {
            foreach ($methodsArray as $methodType => $protectionString) {
                if (!$ddOptions->shouldShowClassMethods($methodType, (bool) $staticMethods)) {
                    continue;
                }

                if (!empty($methods = self::classMethodsToString($object, $methodType, (bool) $staticMethods, $ddOptions, $visited))) {
                    $protectionString = $protectionString . rtrim(" " . $staticString) . " ";
                    
                    if ($ddOptions->isOptionSet(DDO::SHOW_COMMENTS)) {
                        $classOutput .= "    /****************************** " . $protectionString . "methods ******************************/\n";
                    }

                    foreach ($methods as $key => $value) {
                        $classOutput .= "    " . $protectionString . $value . "\n";
                    }

                    $classOutput .= "\n";
                }
            }
        }
        
        if ($classOutput !== "") {
            $output .= "\n" . rtrim($classOutput) . "\n";
        }

        $output .= "}";

        $visited[$hash] = $output;

        return $output;
    }
}

