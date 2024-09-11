<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Types;

use Pst\Core\Enum;
use Pst\Core\Exceptions\InvalidOperationException;

// if the php version is < 8.1 use the function Pst\Core\enum_exists
// if the php version is >= 8.1 use the function enum_exists

if (PHP_VERSION_ID < 80100) {
    if (!function_exists("enum_exists")) {
        function enum_exists(string $name): bool {
            return class_exists($name) && is_a($name, Enum::class, true);
        }
    }
}

if (!function_exists("is_enum")) {
    function is_enum($value): bool {
        return !is_object($value) ? false : enum_exists(get_class($value));
    }
}

//use function Pst\Core\enum_exists;
//use function Pst\Core\is_enum;

use ReflectionClass;
use InvalidArgumentException;

/**
 * Represents a php basic type
 * 
 * @package PST\Core
 * @version 1.0.0
 * @since 1.0.0
 * 
 */
final class Type implements ITypeHint {
    const TYPES = [
        "array"  => ["isArray"  => true,  "isValueType"   => true,  "defaultValue" => []],
        "bool"   => ["isBool"   => true,  "isValueType"   => true,  "defaultValue" => false],
        "float"  => ["isFloat"  => true,  "isNumericType" => true,  "isValueType"  => true,   "defaultValue" => 0.0],
        "int"    => ["isInt"    => true,  "isNumericType" => true,  "isValueType"  => true,   "defaultValue" => 0],
        "null"   => ["isNull"   => true,  "defaultValue"  => null],
        "string" => ["isString" => true,  "isValueType"   => true,  "defaultValue" => ""],
        "void"   => ["isVoid"   => true],
    ];

    private static array $cache = [
        "typesInfo" => [],
        "types" => [],
    ];

    private array $properties = [];

    public static function getTypeInfo(string $name): ?array {
        if (empty($name = trim($name))) {
            throw new InvalidArgumentException("Type name cannot be empty.");
        }

        if (isset(self::$cache["typesInfo"][$name])) {
            return self::$cache["typesInfo"][$name];
        }

        $typeInfo = [
            "namespace"    => "",    "name"            => $name, "fullName" => $name, 
            "defaultValue" => null,
            "isAbstract"   => false, "isArray"         => false, "isBool" => false,
            "isClass"      => false, "isEnum"          => false, "isFloat" => false,
            "isInt"        => false, "isInterface"     => false, "isNull" => false,
            "isObject"     => false, "isReferenceType" => false, "isString" => false,
            "isTrait"      => false, "isValueType"     => false, "isVoid" => false,
        ];

        if (($constTypeInfo = (self::TYPES[$name] ?? null)) !== null) {
            foreach ($constTypeInfo as $key => $value) {
                $typeInfo[$key] = $value;
            }
        } else {
            if (trait_exists($name)) {
                $typeInfo["isTrait"] = true;
                $typeInfo["isAbstract"] = true;

            } else if (enum_exists($name)) {
                $typeInfo["isEnum"] = true;
                $typeInfo["isReferenceType"] = true;

            } else if (class_exists($name)) {
                $typeInfo["isClass"] = true;
                $typeInfo["isObject"] = true;
                $typeInfo["isReferenceType"] = true;
                $typeInfo["isAbstract"] = (new ReflectionClass($name))->isAbstract();

            } else if (interface_exists($name)) {
                $typeInfo["isInterface"] = true;
                $typeInfo["isObject"] = true;
                $typeInfo["isReferenceType"] = true;
                $typeInfo["isAbstract"] = true;

            } else {
                return null;
            }

            $nameParts = explode("\\", ($name = trim($name)));

            foreach ($nameParts as $part) {
                // validate part is a valid php identifier
                if (substr($part, 0, 15) !== "class@anonymous" && !preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $part)) {
                    throw new InvalidArgumentException("Invalid type name near '$name'");
                }
            }

            $typeInfo["name"] = array_pop($nameParts);

            if (($typeInfo["namespace"] = implode("\\", $nameParts)) !== "") {
                $typeInfo["fullName"] = $typeInfo["namespace"] . "\\" . $typeInfo["name"];
            }
            
        }

        //print_r($typeInfo);

        return (self::$cache["typesInfo"][$name] = $typeInfo);
    }

    /**
     * Creates a new instance of Type
     * 
     * @param string $name 
     * 
     * @return void 
     * 
     * @throws InvalidArgumentException 
     */
    private function __construct(string $name) {
        if (empty($name = trim($name))) {
            throw new InvalidArgumentException("Type name cannot be empty.");
        }

        if (isset(self::$cache["types"][$name])) {
            return self::$cache["types"][$name];
        }
        
        if (($this->properties = static::getTypeInfo($name)) === null) {
            throw new InvalidArgumentException("Type '{$name}' does not exist.");
        }
    }

    public function name(): string {
        return $this->properties["name"];
    }

    public function namespace(): string {
        return $this->properties["namespace"];
    }

    public function fullName(): string {
        return $this->properties["fullName"];
    }

    public function __toString(): string {
        return $this->properties["fullName"];
    }

    public function isAbstract(): bool {
        return $this->properties["isAbstract"];
    }

    public function isArray(): bool {
        return $this->properties["isArray"];
    }

    public function isBool(): bool {
        return $this->properties["isBool"];
    }

    public function isClass(): bool {
        return $this->properties["isClass"];
    }

    public function isEnum(): bool {
        return $this->properties["isEnum"];
    }

    public function isFloat(): bool {
        return $this->properties["isFloat"];
    }

    public function isInt(): bool {
        return $this->properties["isInt"];
    }

    public function isInterface(): bool {
        return $this->properties["isInterface"];
    }

    public function isNumericType(): bool {
        return $this->properties["isInt"] || $this->properties["isFloat"];
    }

    public function isNull(): bool {
        return $this->properties["isNull"];
    }

    public function isObject(): bool {
        return ($this->properties["isObject"] ??= false);
    }

    public function isReferenceType(): bool {
        return $this->properties["isReferenceType"];
    }

    public function isString(): bool {
        return $this->properties["isString"];;
    }

    public function isTrait(): bool {
        return $this->properties["isTrait"];
    }

    public function isValueType(): bool {
        return $this->properties["isValueType"];
    }

    public function isVoid(): bool {
        return $this->properties["isVoid"];
    }

    public function defaultValue() {
        if (!array_key_exists($this->properties["name"], self::TYPES)) {
            return null;
        }

        if (!array_key_exists("defaultValue", $this->properties)) {
            throw new InvalidOperationException("Type '{$this->name()}' has no default value.");
        }

        return $this->properties["defaultValue"];
    }

    public function isAssignableFrom(ITypeHint $other): bool {        
        // echo get_class($this) . "::" . $this->fullName() . "->isAssignableFrom(" . get_class($other) . "::" . $other->fullName() . ")\n";

        $toTypeName = $this->fullName();
        $fromTypeName = $other->fullName();

        if ($toTypeName === $fromTypeName) {
            return true;
        } else if ($toTypeName === "void" || $fromTypeName === "void") {
            return false;
        }

        return is_a($fromTypeName, $toTypeName, true);
    }

    public function isAssignableTo(ITypeHint $other): bool {
        // echo get_class($this) . "::" . $this->fullName() . "->isAssignableTo(" . get_class($other) . "::" . $other->fullName() . ")\n";
        return $other->isAssignableFrom($this);
    }

    /**
     * Static factory method to create a new Type instance of the specified type name.
     * 
     * @param $name 
     * @param bool $byValue
     * 
     * @return Type 
     */
    public static function typeOf($typeName, bool $byValue = false): Type {
        if ($byValue) {
            $typeType = gettype($typeName);

            if ($typeType === "object") {
                $typeName = get_class($typeName);
            } else if ($typeType === "array") {
                $typeName = "array";
            } else if ($typeType === "boolean") {
                $typeName = "bool";
            } else if ($typeType === "double") {
                $typeName = "float";
            } else if ($typeType === "integer") {
                $typeName = "int";
            } else if ($typeType === "NULL") {
                $typeName = "null";
            } else if ($typeType === "string") {
                $typeName = "string";
            } else {
                throw new InvalidArgumentException("Unsupported value type: '$typeType'");
            }
        }

        if (!is_string($typeName)) {
            throw new InvalidArgumentException("Type name must be a string.");
        } else if (empty($typeName = trim($typeName))) {
            throw new InvalidArgumentException("Type name cannot be empty.");
        }

        return (self::$cache["types"][$typeName] ??= new Type($typeName));
    }


    /**
     * Static factory method to create a new Type instance of the specified type name.
     * 
     * @param string $name 
     * 
     * @return Type 
     */
    public static function fromTypeName(string $name): Type {
        if (empty($name = trim($name))) {
            throw new InvalidArgumentException("Type name cannot be empty.");
        }

        return (self::$cache["types"][$name] ??= new Type($name));
    }

    /**
     * Static factory method to create a new Type instance from the specified value.
     * 
     * @param $value 
     * 
     * @return Type 
     * 
     * @throws InvalidArgumentException 
     */
    public static function fromValue($value): Type {
        if (is_array($value)) {
            return self::array();
        } else if (is_bool($value)) {
            return self::bool();
        } else if (is_float($value)) {
            return self::float();
        } else if (is_int($value)) {
            return self::int();
        } else if (is_null($value)) {
            return self::null();
        } else if (is_string($value)) {
            return self::string();
        } else if (is_enum($value)) {
            return self::fromTypeName(get_class($value));
        } else if (is_object($value)) {
            
            return self::fromTypeName(get_class($value));
        } else {
            throw new InvalidArgumentException("Unsupported value type: " . gettype($value) . ", " . print_r($value, true));
        }
    }

    public static function array(): Type {
        return (self::$cache["types"]["array"] ??= new Type("array"));
    }

    public static function bool(): Type {
        return (self::$cache["types"]["bool"] ??= new Type("bool"));
    }

    public static function float(): Type {
        return (self::$cache["types"]["float"] ??= new Type("float"));
    }

    public static function int(): Type {
        return (self::$cache["types"]["int"] ??= new Type("int"));
    }

    public static function null(): Type {
        return (self::$cache["types"]["null"] ??= new Type("null"));
    }

    public static function string(): Type {
        return (self::$cache["types"]["string"] ??= new Type("string"));
    }

    public static function void(): Type {
        return (self::$cache["types"]["void"] ??= new Type("void"));
    }

    public static function interface(string $name): Type {
        if (empty($name = trim($name))) {
            throw new InvalidArgumentException("Type name cannot be empty.");
        }

        if (!interface_exists($name)) {
            throw new InvalidArgumentException("Interface '{$name}' does not exist.");
        }

        return (self::$cache["types"][$name] ??= new Type($name));
    }

    public static function class(string $name): Type {
        if (empty($name = trim($name))) {
            throw new InvalidArgumentException("Type name cannot be empty.");
        }

        if (!class_exists($name)) {
            throw new InvalidArgumentException("Class '{$name}' does not exist.");
        }

        return (self::$cache["types"][$name] ??= new Type($name));
    }

    public static function trait(string $name): Type {
        if (empty($name = trim($name))) {
            throw new InvalidArgumentException("Type name cannot be empty.");
        }

        if (!trait_exists($name)) {
            throw new InvalidArgumentException("Trait '{$name}' does not exist.");
        }

        return (self::$cache["types"][$name] ??= new Type($name));
    }

    public static function enum(string $name): Type {
        if (empty($name = trim($name))) {
            throw new InvalidArgumentException("Type name cannot be empty.");
        }

        if (!enum_exists($name)) {
            throw new InvalidArgumentException("Enum '{$name}' does not exist.");
        }

        return (self::$cache["types"][$name] ??= new Type($name));
    }



    
    

    // /**
    //  * Static factory method to create a new Type instance of enum.
    //  * 
    //  * @return Type 
    //  */
    // public static function enum(): Type {
    //     return (self::$typeCache["enum"] ??= new Type("enum"));
    // }
}