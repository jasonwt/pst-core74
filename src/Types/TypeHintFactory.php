<?php

declare(strict_types=1);

namespace Pst\Core\Types;

use Pst\Core\Caching\Caching;

use Pst\Core\Exceptions\NotImplementedException;

use InvalidArgumentException;

final class TypeHintFactory {
    private static array $typeNameCache = [];

    // Static class
    private function __construct() {}

    /**
     * Creates a ITypeHint instance
     * 
     * @param mixed $input
     * @param bool $byValue
     * 
     * @return ITypeHint
     * 
     * @throws InvalidArgumentException
     */
    public static function new($input, bool $byValue = false): ITypeHint {
        $inputType = gettype($input);

        if ($byValue) {
            if ($inputType === "object") {
                $input = get_class($input);
            } else if ($inputType === "NULL") {
                $input = "null";
            } else if ($inputType === "boolean") {
                $input = "bool";
            } else if ($inputType === "integer") {
                $input = "int";
            } else if ($inputType === "double") {
                $input = "float";
            } else if ($inputType === "array") {
                $input = "array";
            } else if ($inputType === "resource") {
                $input = "resource";
            } else if ($inputType === "unknown type") {
                $input = "mixed";
            } 

            return Caching::getWithSet($input,function() use ($input) {
                return Type::new($input);
            }, "ITypeHint::create");
        } else if ($inputType !== "string") {
            throw new \InvalidArgumentException("Type hint must be a string or any type with the byValue flag set to true");
        } else if (empty($input = trim($input))) {
            throw new \InvalidArgumentException("Type hint cannot be empty");
        }

        return Caching::getWithSet($input,function() use ($input) {
            if (in_array($input, SpecialType::SPECIAL_TYPES)) {
                return SpecialType::new($input);
            }

            $pipeCount = substr_count($input, "|");
            $ampCount = substr_count($input, "&");

            if ($pipeCount == 0 && $ampCount == 0) {
                return Type::new($input);
            } else if ($ampCount == 0) {
                return TypeUnion::tryParseTypeName($input);
            }

            throw new NotImplementedException();
        }, "ITypeHint::create");
    }

    /**
     * Tries to parse a string into a ITypeHint instance
     * 
     * @param string $input
     * 
     * @return ITypeHint|null
     */
    public static function tryParseTypeName(string $input): ?ITypeHint {
        if (empty($input = trim($input))) {
            return null;
        }

        return Caching::getWithSet($input,function() use ($input) {
            if (in_array($input, SpecialType::SPECIAL_TYPES)) {
                return SpecialType::tryParseTypeName($input);
            }

            $input = str_replace("?", "null|" , $input);

            $pipeCount = substr_count($input, "|");
            $ampCount = substr_count($input, "&");

            if ($pipeCount == 0 && $ampCount == 0) {
                return Type::tryParseTypeName($input);
            } else if ($ampCount == 0) {
                return TypeUnion::tryParseTypeName($input);
            } else if ($pipeCount == 0) {
                return TypeIntersection::tryParseTypeName($input);
            }

            return null;
        }, "ITypeHint::create");
    }

    /**
     * Creates a new TypeUnion instance
     * 
     * @param string|ITypeHint ...$types 
     * 
     * @return TypeUnion 
     * 
     * @throws InvalidArgumentException 
     */
    public static function union(... $types): TypeUnion {
        return TypeUnion::create(...$types);
    }

    /**
     * Creates a new TypeIntersection instance
     * 
     * @param string|ITypeHint ...$types 
     * 
     * @return TypeIntersection 
     * 
     * @throws InvalidArgumentException 
     */
    public static function intersection(... $types): TypeIntersection {
        return TypeIntersection::new(...$types);
    }

    /**
     * Creates a new undefined instance
     * 
     * @param string $typeName 
     * 
     * @return ITypeHint 
     * 
     * @throws InvalidArgumentException
     */
    public static function undefined(bool $nullable = false): ITypeHint {
        return $nullable ? TypeUnion::tryParseTypeName("undefined|null") : SpecialType::tryParseTypeName("undefined");
    }

    /**
     * Creates a new mixed instance
     * 
     * @param string $typeName 
     * 
     * @return ITypeHint 
     * 
     * @throws InvalidArgumentException
     */
    public static function mixed(bool $nullable = false): ITypeHint {
        return $nullable ? TypeUnion::tryParseTypeName("mixed|null") : SpecialType::tryParseTypeName("mixed");
    }

    /**
     * Creates a new key type instance
     * 
     * @param bool $nullable 
     * 
     * @return ITypeHint 
     * 
     * @throws InvalidArgumentException
     */
    public static function keyTypes(bool $nullable = false): ITypeHint {
        return $nullable ? TypeUnion::tryParseTypeName("int|string|null") : TypeUnion::tryParseTypeName("int|string");
    }

    /**
     * Creates a new void instance
     * 
     * @param string $typeName 
     * 
     * @return ITypeHint 
     * 
     * @throws InvalidArgumentException
     */
    public static function void(): ITypeHint {
        return SpecialType::tryParseTypeName("void");
    }

    /**
     * Creates a new object type instance
     * 
     * @param bool $nullable 
     * 
     * @return ITypeHint 
     * 
     * @throws InvalidArgumentException
     */
    public static function object(bool $nullable = false): ITypeHint {
        return $nullable ? TypeUnion::tryParseTypeName("object|null") : SpecialType::tryParseTypeName("object");
    }

    /**
     * Creates a new resource type instance
     * 
     * @param bool $nullable 
     * 
     * @return ITypeHint 
     * 
     * @throws InvalidArgumentException
     */
    public static function resource(bool $nullable = false): ITypeHint {
        return $nullable ? TypeUnion::tryParseTypeName("resource|null") : SpecialType::tryParseTypeName("resource");
    }

    /**
     * Creates a new class type instance
     * 
     * @param bool $nullable 
     * @param string|null $className
     * 
     * @return ITypeHint 
     * 
     * @throws InvalidArgumentException
     */
    public static function class(bool $nullable = false, ?string $className = null): ITypeHint {
        return $nullable ? TypeUnion::tryParseTypeName(($className ?? "class") . "|null") : (
            is_null($className) ? SpecialType::class() : Type::class($className)
        );
    }

    /**
     * Creates a new interface type instance
     * 
     * @param bool $nullable 
     * @param string|null $interfaceName
     * 
     * @return ITypeHint 
     * 
     * @throws InvalidArgumentException
     */
    public static function interface(bool $nullable = false, ?string $interfaceName = null): ITypeHint {
        return $nullable ? TypeUnion::tryParseTypeName(($interfaceName ?? "interface") . "|null") : (
            is_null($interfaceName) ? SpecialType::interface() : Type::interface($interfaceName)
        );
    }

    /**
     * Creates a new trait type instance
     * 
     * @param bool $nullable 
     * @param string|null $traitName
     * 
     * @return ITypeHint 
     * 
     * @throws InvalidArgumentException
     */
    public static function trait(bool $nullable = false, ?string $traitName = null): ITypeHint {
        return $nullable ? TypeUnion::tryParseTypeName(($traitName ?? "trait") . "|null") : (
            is_null($traitName) ? SpecialType::trait() : Type::trait($traitName)
        );
    }

    /**
     * Creates a new enum type instance
     * 
     * @param bool $nullable 
     * @param string|null $enumName
     * 
     * @return ITypeHint 
     * 
     * @throws InvalidArgumentException
     */
    public static function enum(bool $nullable = false, ?string $enumName = null): ITypeHint {
        return $nullable ? TypeUnion::tryParseTypeName(($enumName ?? "enum") . "|null") : (
            is_null($enumName) ? SpecialType::enum() : Type::enum($enumName)
        );
    }

    /**
     * Creates a new null type instance
     * 
     * @return ITypeHint 
     * 
     * @throws InvalidArgumentException
     */
    public static function null(): ITypeHint {
        return Type::tryParseTypeName("null");
    }

    /**
     * Creates a new array type instance
     * 
     * @param bool $nullable 
     * 
     * @return ITypeHint 
     * 
     * @throws InvalidArgumentException
     */
    public static function array(bool $nullable = false): ITypeHint {
        return $nullable ? TypeUnion::tryParseTypeName("array|null") : Type::array();
    }

    /**
     * Creates a new bool type instance
     * 
     * @param bool $nullable 
     * 
     * @return ITypeHint 
     * 
     * @throws InvalidArgumentException
     */
    public static function bool(bool $nullable = false): ITypeHint {
        return $nullable ? TypeUnion::tryParseTypeName("bool|null") : Type::bool();
    }

    /**
     * Creates a new float type instance
     * 
     * @param bool $nullable 
     * 
     * @return ITypeHint 
     * 
     * @throws InvalidArgumentException
     */
    public static function float(bool $nullable = false): ITypeHint {
        return $nullable ? TypeUnion::tryParseTypeName("float|null") : Type::float();
    }

    /**
     * Creates a new int type instance
     * 
     * @param bool $nullable 
     * 
     * @return ITypeHint 
     * 
     * @throws InvalidArgumentException
     */
    public static function int(bool $nullable = false): ITypeHint {
        return $nullable ? TypeUnion::tryParseTypeName("int|null") : Type::int();
    }

    /**
     * Creates a new string type instance
     * 
     * @param bool $nullable 
     * 
     * @return ITypeHint 
     * 
     * @throws InvalidArgumentException
     */
    public static function string(bool $nullable = false): ITypeHint {
        return $nullable ? TypeUnion::tryParseTypeName("string|null") : Type::string();
    }
}