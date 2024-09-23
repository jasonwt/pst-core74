<?php

declare(strict_types=1);

namespace Pst\Core\Types;

use Pst\Core\Interfaces\ITryParse;
use Pst\Core\Exceptions\NotImplementedException;

use InvalidArgumentException;

final class TypeHintFactory implements ITryParse{
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

            if (isset(self::$typeNameCache[$input])) {
                return self::$typeNameCache[$input];
            }

            return (self::$typeNameCache[$input] = Type::new($input));

        } else if ($inputType !== "string") {
            throw new \InvalidArgumentException("Type hint must be a string or any type with the byValue flag set to true");
        } else if (empty($input = trim($input))) {
            throw new \InvalidArgumentException("Type hint cannot be empty");
        }

        if (isset(self::$typeNameCache[$input])) {
            return self::$typeNameCache[$input];
        }

        if (in_array($input, SpecialType::SPECIAL_TYPES)) {
            return self::$typeNameCache[$input] = SpecialType::new($input);
        }

        $pipeCount = substr_count($input, "|");
        $ampCount = substr_count($input, "&");

        if ($pipeCount == 0 && $ampCount == 0) {
            return (self::$typeNameCache[$input] = Type::new($input));
        } else if ($ampCount == 0) {
            return (self::$typeNameCache[$input] = TypeUnion::tryParse($input));
        }

        throw new NotImplementedException();
    }

    /**
     * Tries to parse a string into a ITypeHint instance
     * 
     * @param string $input
     * 
     * @return ITypeHint|null
     */
    public static function tryParse(string $input): ?ITypeHint {
        if (empty($input = trim($input))) {
            return null;
        }

        if (isset(self::$typeNameCache[$input])) {
            return self::$typeNameCache[$input];
        }

        $input = str_replace("?", "null|" , $input);

        $pipeCount = substr_count($input, "|");
        $ampCount = substr_count($input, "&");

        if ($pipeCount == 0 && $ampCount == 0) {
            if (in_array($input, SpecialType::SPECIAL_TYPES)) {
                
                return (self::$typeNameCache[$input] = SpecialType::new($input));
            }

            return (self::$typeNameCache[$input] = Type::new($input));
        } else if ($ampCount == 0) {
            
            return (self::$typeNameCache[$input] = TypeUnion::tryParse($input));
        } else if ($pipeCount == 0) {
            
            return (self::$typeNameCache[$input] = TypeIntersection::tryParse($input));
        }

        throw new NotImplementedException();
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
        return TypeUnion::new(...$types);
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
        return $nullable ? TypeUnion::tryParse("undefined|null") : SpecialType::tryParse("undefined");
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
        return $nullable ? TypeUnion::tryParse("mixed|null") : SpecialType::tryParse("mixed");
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
        return $nullable ? TypeUnion::tryParse("int|string|null") : TypeUnion::tryParse("int|string");
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
        return SpecialType::tryParse("void");
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
        return $nullable ? TypeUnion::tryParse("object|null") : SpecialType::tryParse("object");
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
        return $nullable ? TypeUnion::tryParse("resource|null") : SpecialType::tryParse("resource");
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
        return $nullable ? TypeUnion::tryParse(($className ?? "class") . "|null") : (
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
        return $nullable ? TypeUnion::tryParse(($interfaceName ?? "interface") . "|null") : (
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
        return $nullable ? TypeUnion::tryParse(($traitName ?? "trait") . "|null") : (
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
        return $nullable ? TypeUnion::tryParse(($enumName ?? "enum") . "|null") : (
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
        return Type::tryParse("null");
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
        return $nullable ? TypeUnion::tryParse("array|null") : Type::array();
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
        return $nullable ? TypeUnion::tryParse("bool|null") : Type::bool();
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
        return $nullable ? TypeUnion::tryParse("float|null") : Type::float();
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
        return $nullable ? TypeUnion::tryParse("int|null") : Type::int();
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
        return $nullable ? TypeUnion::tryParse("string|null") : Type::string();
    }
}