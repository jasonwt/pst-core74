<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Types;

use ReflectionNamedType;
use LogicException;
use InvalidArgumentException;

/**
 * Represents a type hint that can be used to specify the type of a variable, parameter, or return value.
 * 
 * @package PST\Core
 * @version 1.0.0
 * @since 1.0.0
 * 
 */
final class TypeHint implements ITypeHint {
    const TYPES = [
        "mixed" => true,
        "undefined" => true,
        "object" => true,
        // "class" => true,
        // "interface" => true,
        // "trait" => true,
        // "enum" => true,
        // "resource" => true
    ];

    private static array $cache = [
        "typeHints" => []
    ];

    private array $types;
    private string $fullName = "";

    /**
     * Creates a new instance of TypeHint
     * 
     * @param string|Type|TypeHint ...$types 
     * 
     * @return void 
     */
    private function __construct(string ...$types) {

        $typeHintInfo = self::getTypeHintInfo(...$types);

        $this->types = $typeHintInfo["types"];
        $this->fullName = implode("|", array_keys($this->types));
    }


    /**
     * Returns the full name of the current type hint.
     * 
     * @return string 
     */
    public function fullName(): string {
        return $this->fullName;
    }

    /**
     * Returns a string representation of the current type hint.
     * 
     * @return string 
     */
    public function __toString(): string {
        return $this->fullName;
    }

    /**
     * Returns true if the current type can be assigned from the specified type hint.
     * 
     * @param ITypeHint $other
     * 
     * @return bool 
     * 
     * @throws LogicException
     * 
     */
    public function isAssignableFrom(ITypeHint $other): bool {
        // echo get_class($this) . "::" . $this->fullName() . "->isAssignableFrom(" . get_class($other) . "::" . $other->fullName() . ")\n";
        //  true if any of the following conditions is true:
        //      a.) $other and the current instance represent the same type.
        //      b.) $other inherits from the current instance; 
        //      c.) $other inherits from a succession of one or more classes that inherit from the current instance.
        //      d.) The current instance is an interface that $other implements.
        //      f.) $other represents a value type, and the current instance represents Nullable<$other>.
        // false if none of these conditions are true, or if $other is null.

        $toTypeName = $this->fullName();

        $fromTypeParts = explode("|", $other->fullName());

        foreach ($fromTypeParts as $fromTypeName) {
            $fromTypeIsHint = isset(self::TYPES[$fromTypeName]);

            foreach ($this->types as $toTypePart) {
                if ($fromTypeName == $toTypePart) {
                    continue 2;
                } else if ($fromTypeName === "void" && $toTypePart === "void") {
                    continue 2;
                } else if ($fromTypeName === "void" || $toTypePart === "void") {
                    continue;
                } if ($toTypePart === "mixed" || $toTypePart === "undefined") {
                    continue 2;
                } else if ($fromTypeName === "mixed" || $fromTypeName === "undefined") {
                    continue;
                }
    
                $toTypeIsHint = isset(TypeHint::TYPES[$toTypePart]);
                
                if (!$fromTypeIsHint && !$toTypeIsHint) {
                    if (is_a($fromTypeName, $toTypePart, true)) {
                        continue 2;
                    }

                    continue;
                }
    
                if ($toTypeIsHint) {
                    $fromType = Type::fromTypeName($fromTypeName);
    
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

    /**
     * Returns true if the current type can be assigned to the specified type hint.
     * 
     * @param ITypeHint $other
     * 
     * @return bool 
     * 
     * @throws LogicException
     * 
     */
    public function isAssignableTo(ITypeHint $other): bool {
        // // echo get_class($this) . "::" . $this->fullName() . "->isAssignableTo(" . get_class($other) . "::" . $other->fullName() . ")\n";

        // if ($other instanceof Type) {
        //     if (count($this->types) > 1) {
        //         return false;
        //     }

        //     $thisTypeName = array_key_first($this->types);

        //     if ($thisTypeName === "mixed" || $thisTypeName === "undefined") {
        //         return false;
        //     } else if ($thisTypeName === "object") {
        //         return $other->isObject();
        //     }

        //     return $other->isAssignableFrom($this->types[$thisTypeName]);
        // }

        return $other->isAssignableFrom($this);
    }

    public static function getTypeHintInfo(... $typeHints): ?array {
        $typeHintsInfo = array_reduce($typeHints, function($carry, $typeHint) {
            if ($typeHint instanceof ITypeHint) {
                $typeHint = $typeHint->fullName();
            } else if (!is_string($typeHint)) {
                throw new InvalidArgumentException("Invalid type hint '" . gettype($typeHint) . "'");
            }

            foreach (explode("|", trim($typeHint)) as $typeName) {
                if (empty($typeName = trim($typeName))) {
                    throw new InvalidArgumentException("Invalid type hint '$typeName'");
                }

                if ($typeName[0] === "?") {
                    $carry["types"]["null"] ??= "null";
                    if (empty($typeName = trim(substr($typeName, 1)))) {
                        throw new InvalidArgumentException("Invalid type hint '$typeName'");
                    }
                }

                if (($typeInfo = Type::getTypeInfo($typeName)) !== null) {
                    $carry["types"][$typeName] ??= $typeName;
                } else if (!isset(self::TYPES[$typeName])) {
                    throw new InvalidArgumentException("Invalid type hint '$typeName'");
                } else {
                    $carry["types"][$typeName] ??= $typeName;
                }

                if (isset($carry[$typeName])) {
                    $carry[$typeName] = true;

                    if (count($carry["types"]) > 1) {
                        throw new InvalidArgumentException("mixed, undefined and void types cannot be combined with other types");
                    }
                }
            }
            
            return $carry;
        }, ["types" => [], "mixed" => false, "void" => false, "undefined" => false]);

        return $typeHintsInfo;
    }

    public static function typeHintOf($typeHintName, bool $byValue = false): ITypeHint {
        //echo "typeHintOf(" . print_r($typeHintName, true) . ", $byValue)\n";
        if ($byValue) {
            $typeType = gettype($typeHintName);

            if ($typeType === "object") {
                $typeHintName = get_class($typeHintName);
            } else if ($typeType === "array") {
                $typeHintName = "array";
            } else if ($typeType === "boolean") {
                $typeHintName = "bool";
            } else if ($typeType === "double") {
                $typeHintName = "float";
            } else if ($typeType === "integer") {
                $typeHintName = "int";
            } else if ($typeType === "NULL") {
                $typeHintName = "null";
            } else if ($typeType === "string") {
                $typeHintName = "string";
            } else {
                throw new InvalidArgumentException("Unsupported value type: '$typeType'");
            }

            return (self::$cache["typeHints"][$typeHintName] ??= Type::fromTypeName($typeHintName));
        }

        if (!is_string($typeHintName)) {
            throw new InvalidArgumentException("Invalid type hint '$typeHintName'");
        } else if (empty($typeHintName = trim($typeHintName))) {
            throw new InvalidArgumentException("Invalid type hint '$typeHintName'");
        }

        if (($typeHint = (self::$cache["typeHints"][$typeHintName] ??= null)) === null) {
            if (($typeInfo = Type::getTypeInfo($typeHintName)) !== null) {
                return (self::$cache["typeHints"][$typeHintName] = Type::fromTypeName($typeHintName));   
            }

            $typeInfo = self::getTypeHintInfo($typeHintName);

            ksort($typeInfo["types"]);

            $typeHint = (self::$cache["typeHints"][$typeHintName] = new TypeHint(...array_values($typeInfo["types"])));
        }

        return $typeHint;
    }







    
    public static function fromTypeNames(string ...$typeNames): TypeHint {
        if (count($typeNames) === 0) {
            throw new InvalidArgumentException("At least one type hint is required.");
        }

        $typeHintKey = implode("|", $typeNames);

        if (isset(self::$cache[$typeHintKey])) {
            return self::$cache[$typeHintKey];
        }

        $typeNames = array_keys(array_reduce($typeNames, function($carry, $arg) {
            return $carry + array_reduce(explode("|", trim($arg)), function($carry, $v) {
                if (empty($v = trim($v))) {
                    throw new InvalidArgumentException("Invalid type hint '$v'");
                }

                if ($v[0] === "?") {
                    $carry["null"] ??= true;
                    $v = substr($v, 1);
                }

                if (empty($v = trim($v))) {
                    throw new InvalidArgumentException("Invalid type hint '$v'");
                }

                $carry[$v] ??= true;

                return $carry;
            }, []);
        }, []));

        return self::$cache[$typeHintKey] = new TypeHint(...$typeNames);
    }

    /**
     * Returns a TypeHint instance that represents the union of the specified types.
     * 
     * @param string|Type|TypeHint ...$types 
     * 
     * @return TypeHint 
     * 
     * @throws InvalidArgumentException 
     */
    public static function union(...$types): TypeHint {
        return new TypeHint(...$types);
    }

    public static function fromReflectionNamedType(?ReflectionNamedType $reflectionNamedType): ?ITypeHint {
        if ($reflectionNamedType === null) {
            return new TypeHint("undefined");
        }

        $typeFullName = $reflectionNamedType->getName();

        if ($reflectionNamedType->allowsNull()) {
            $typeFullName .= "|null";
        }
        
        return new TypeHint($typeFullName);
    }

    public static function mixed(): ITypeHint {
        return self::typeHintOf("mixed");
    }

    public static function undefined(): ITypeHint {
        return self::typeHintOf("undefined");
    }

    public static function keyTypes(bool $nullable = false): ITypeHint {
        return self::typeHintOf(($nullable ? "int|string|null" : "int|string"));
    }

    public static function numericTypes(bool $nullable = false): ITypeHint {
        return self::typeHintOf(($nullable ? "int|float|null" : "int|float"));
    }

    public static function object(bool $nullable = false): ITypeHint {
        return self::typeHintOf(($nullable ? "object|null" : "object"));
    }

    public static function array(bool $nullable = false): ITypeHint {
        return self::typeHintOf(($nullable ? "array|null" : "array"));
    }

    public static function bool(bool $nullable = false): ITypeHint {
        return self::typeHintOf(($nullable ? "bool|null" : "bool"));
    }

    public static function float(bool $nullable = false): ITypeHint {
        return self::typeHintOf(($nullable ? "float|null" : "float"));
    }

    public static function int(bool $nullable = false): ITypeHint {
        return self::typeHintOf(($nullable ? "int|null" : "int"));
    }

    public static function null(): ITypeHint {
        return Type::null();
    }

    public static function string(bool $nullable = false): ITypeHint {
        return self::typeHintOf(($nullable ? "string|null" : "string"));
    }

    public static function void(): ITypeHint {
        return Type::void();
    }

    public static function interface(string $interfaceName, bool $nullable = false ): ITypeHint {
        return self::typeHintOf(Type::interface($interfaceName)->fullName() . ($nullable ? "|null" : ""));
    }

    public static function class(string $className, bool $nullable = false ): ITypeHint {
        return self::typeHintOf(Type::class($className)->fullName() . ($nullable ? "|null" : ""));
    }

    public static function trait(string $traitName, bool $nullable = false ): ITypeHint {
        return self::typeHintOf(Type::trait($traitName)->fullName() . ($nullable ? "|null" : ""));
    }

    public static function enum(string $enumName, bool $nullable = false ): ITypeHint {
        return self::typeHintOf(Type::enum($enumName)->fullName() . ($nullable ? "|null" : ""));
    }
};