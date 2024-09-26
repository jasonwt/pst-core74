<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Types;

use Pst\Core\Enum;
use Pst\Core\Interfaces\ITryParse;
use Pst\Core\CoreObject;
use Pst\Core\Interfaces\ICoreObject;

use Pst\Core\Exceptions\InvalidOperationException;

use ReflectionClass;
use InvalidArgumentException;

if (!function_exists("enum_exists")) {
    function enum_exists(string $name): bool {
        return is_a($name, Enum::class, true);
    }
}

/**
 * Represents a php basic type
 * 
 * @package PST\Core
 * @version 1.0.0
 * @since 1.0.0
 * 
 */
final class Type extends CoreObject implements ICoreObject, ITypeHint, ITryParse {
    private static array $typeNameCache = [];
    private static array $getTypeInfoCache = [];

    const TYPES = [
        "array"  => ["isArray"  => true,  "isValueType"   => true,  "defaultValue" => []],
        "bool"   => ["isBool"   => true,  "isValueType"   => true,  "defaultValue" => false],
        "float"  => ["isFloat"  => true,  "isNumericType" => true,  "isValueType"  => true,   "defaultValue" => 0.0],
        "int"    => ["isInt"    => true,  "isNumericType" => true,  "isValueType"  => true,   "defaultValue" => 0],
        "null"   => ["isNull"   => true,  "defaultValue"  => null],
        "string" => ["isString" => true,  "isValueType"   => true,  "defaultValue" => ""],
        "void"   => ["isVoid"   => true],
    ];

    private string $namespace = "";
    private string $name = "";
    private string $fullName = "";
    private $defaultValue = null;

    private bool $isAbstract = false;
    private bool $isArray = false;
    private bool $isBool = false;
    private bool $isClass = false;
    private bool $isEnum = false;
    private bool $isFloat = false;
    private bool $isInt = false;
    private bool $isInterface = false;
    private bool $isNull = false;
    private bool $isObject = false;
    private bool $isReferenceType = false;
    private bool $isResource = false;
    private ?bool $isScalerType = null;
    private bool $isString = false;
    private bool $isTrait = false;
    private bool $isValueType = false;
    private bool $isVoid = false;

    /**
     * Gets the type info for the provided type name
     * 
     * @param string $name 
     * 
     * @return array|null 
     * 
     * @throws InvalidArgumentException 
     */
    public static function getTypeInfo(string $name): ?array {
        if (empty($name = trim($name))) {
            throw new InvalidArgumentException("Type name cannot be empty.");
        }

        if (isset(self::$getTypeInfoCache[$name])) {
            return self::$getTypeInfoCache[$name];
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

            } else if (enum_exists($name) ||  is_a($name, Enum::class, true)) {
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

        return (self::$getTypeInfoCache[$name] = $typeInfo);
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

        if (($properties = (self::$getTypeInfoCache[$name] ?? static::getTypeInfo($name))) === null) {
            throw new InvalidArgumentException("Type '{$name}' does not exist.");
        }
        
        foreach ($properties as $propertyName => $propertyValue) {
            $this->{$propertyName} = $propertyValue;
        }
    }

    public function name(): string {
        return $this->name;
    }

    public function namespace(): string {
        return $this->namespace;
    }

    public function fullName(): string {
        return $this->fullName;
    }

    public function __toString(): string {
        return $this->fullName;
    }

    public function isAbstract(): bool {
        return $this->isAbstract;
    }

    public function isArray(): bool {
        return $this->isArray;
    }

    public function isBool(): bool {
        return $this->isBool;
    }

    public function isClass(): bool {
        return $this->isClass;
    }

    public function isEnum(): bool {
        return $this->isEnum;
    }

    public function isFloat(): bool {
        return $this->isFloat;
    }

    public function isInt(): bool {
        return $this->isInt;
    }

    public function isInterface(): bool {
        return $this->isInterface;
    }

    public function isNumericType(): bool {
        return $this->isInt || $this->isFloat;
    }

    public function isNull(): bool {
        return $this->isNull;
    }

    public function isObject(): bool {
        return $this->isObject;
    }

    public function isReferenceType(): bool {
        return $this->isReferenceType;
    }

    public function isScalerType(): bool {
        return $this->isBool || $this->isInt || $this->isFloat || $this->isString;
    }

    public function isString(): bool {
        return $this->isString;
    }

    public function isTrait(): bool {
        return $this->isTrait;
    }

    public function isValueType(): bool {
        return $this->isValueType;
    }

    public function isVoid(): bool {
        return $this->isVoid;
    }

    public function isResource(): bool {
        return $this->isResource;
    }

    public function defaultValue() {
        if ($this->fullName === "void") {
            throw new InvalidOperationException("Type '{$this->name()}' has no default value.");
        } else if ($this->fullName === "null") {
            return null;
        }

        if (!array_key_exists($this->name, self::TYPES)) {
            return null;
        }

        return $this->defaultValue;
    }

    public function isAssignableFrom(ITypeHint $other): bool {        
        // echo get_class($this) . "::" . $this->fullName() . "->isAssignableFrom(" . get_class($other) . "::" . $other->fullName() . ")\n";

        if (($toTypeName = $this->fullName()) === ($fromTypeName = $other->fullName())) {
            return true;
        }

        if (!$other instanceof Type) {
            return false;
        }

        return is_a($fromTypeName, $toTypeName, true);   
    }

    public function isAssignableTo(ITypeHint $other): bool {
        return $other->isAssignableFrom($this);
    }


    /**
     * Gets the type of the provided value
     * 
     * @param mixed $typeName 
     * 
     * @return Type 
     * 
     * @throws InvalidArgumentException 
     */
    public static function typeOf($typeName): Type {
        $inputType = gettype($typeName);

        if ($inputType === "object") {
            $inputType = get_class($typeName);
        } else if ($inputType === "string") {
            $inputType = "string";
        } else if ($inputType === "NULL") {
            $inputType = "null";
        } else if ($inputType === "boolean") {
            $inputType = "bool";
        } else if ($inputType === "integer") {
            $inputType = "int";
        } else if ($inputType === "double") {
            $inputType = "float";
        } else if ($inputType === "array") {
            $inputType = "array";
        } else if ($inputType === "resource") {
            $inputType = "resource";
        } else {
            throw new InvalidArgumentException("Unsupported value type: '$inputType'");
        } 

        return (self::$typeNameCache[$inputType] ??= new Type($inputType));
    }

    /**
     * Creates a new instance of Type
     * 
     * @param mixed $input 
     * @param bool $byValue 
     * 
     * @return Type 
     * 
     * @throws InvalidArgumentException 
     */
    public static function new($input, bool $byValue = false): Type {
        $inputType = gettype($input);

        if ($byValue) {
            return self::typeOf($input, true);
        } else if ($inputType !== "string") {
            throw new InvalidArgumentException("Type hint must be a string or any type with the byValue flag set to true");
        } else if (empty($input = trim($input))) {
            throw new InvalidArgumentException("Type hint cannot be empty");
        }

        return (self::$typeNameCache[$input] ??= new Type($input));
    }

    /**
     * Tries to parse a string into a Type instance
     * 
     * @param string $input 
     * 
     * @return Type|null 
     */
    public static function tryParse(string $input): ?Type {
        if (empty($input = trim($input))) {
            return null;
        }

        if (!isset(self::$typeNameCache[$input])) {
            if ((self::$getTypeInfoCache[$input] ??= self::getTypeInfo($input)) === null) {
                return null;
            }
        }

        return (self::$typeNameCache[$input] = new Type($input));
    }
    
    public static function array(): Type {
        return (self::$typeNameCache["array"] ??= new Type("array"));
    }

    public static function bool(): Type {
        return (self::$typeNameCache["bool"] ??= new Type("bool"));
    }

    public static function float(): Type {
        return (self::$typeNameCache["float"] ??= new Type("float"));
    }

    public static function int(): Type {
        return (self::$typeNameCache["int"] ??= new Type("int"));
    }

    public static function null(): Type {
        return (self::$typeNameCache["null"] ??= new Type("null"));
    }

    public static function string(): Type {
        return (self::$typeNameCache["string"] ??= new Type("string"));
    }

    public static function void(): Type {
        return (self::$typeNameCache["void"] ??= new Type("void"));
    }

    /**
     * Creates a new Type instance for the provided class name
     * 
     * @param string $name 
     * 
     * @return Type 
     * 
     * @throws InvalidArgumentException 
     */
    public static function interface(string $name): Type {
        if (empty($name = trim($name))) {
            throw new InvalidArgumentException("Type name cannot be empty.");
        } else if (!interface_exists($name)) {
            throw new InvalidArgumentException("Interface '{$name}' does not exist.");
        }

        return (self::$typeNameCache[$name] ??= new Type($name));
    }

    /**
     * Creates a new Type instance for the provided class name
     * 
     * @param string $name 
     * 
     * @return Type 
     * 
     * @throws InvalidArgumentException 
     */
    public static function class(string $name): Type {
        if (empty($name = trim($name))) {
            throw new InvalidArgumentException("Type name cannot be empty.");
        } else if (!class_exists($name)) {
            throw new InvalidArgumentException("Class '{$name}' does not exist.");
        }

        return (self::$typeNameCache[$name] ??= new Type($name));
    }

    /**
     * Creates a new Type instance for the provided trait name
     * 
     * @param string $name 
     * 
     * @return Type 
     * 
     * @throws InvalidArgumentException 
     */
    public static function trait(string $name): Type {
        if (empty($name = trim($name))) {
            throw new InvalidArgumentException("Type name cannot be empty.");
        } else if (!trait_exists($name)) {
            throw new InvalidArgumentException("Trait '{$name}' does not exist.");
        }

        return (self::$typeNameCache[$name] ??= new Type($name));
    }

    /**
     * Creates a new Type instance for the provided enum name
     * 
     * @param string $name 
     * 
     * @return Type 
     * 
     * @throws InvalidArgumentException 
     */
    public static function enum(string $name): Type {
        if (empty($name = trim($name))) {
            throw new InvalidArgumentException("Type name cannot be empty.");
        } else if (!enum_exists($name)) {
            throw new InvalidArgumentException("Enum '{$name}' does not exist.");
        }

        return (self::$typeNameCache[$name] ??= new Type($name));
    }
}