<?php

declare(strict_types=1);

namespace Pst\Core\Types;

use Pst\Core\CoreObject;
use Pst\Core\Caching\Caching;

use InvalidArgumentException;


class SpecialType extends CoreObject implements ITypeHint {
    const SPECIAL_TYPES = [
        "undefined" => "undefined",
        "mixed" => "mixed",
        "void" => "void",
        "object" => "object",
        "class" => "class",
        "interface" => "interface",
        "trait" => "trait",
        "enum" => "enum",
        "resource" => "resource",
        "variatic" => "variatic", // TODO: not implemented yet
    ];

    private string $fullName = "";

    /**
     * Creates a new TypeUnion instance
     * 
     * @param string $typeName
     * 
     * @return void 
     * 
     * @throws InvalidArgumentException
     */
    private function __construct(string $typeName) {
        if (empty($typeName = trim($typeName))) {
            throw new InvalidArgumentException("Empty type name provided");
        } else if (!isset(self::SPECIAL_TYPES[$typeName])) {
            throw new InvalidArgumentException("Invalid special type name provided: '$typeName'");
        }

        $this->fullName = $typeName;
    }

    public function typeGroup(): string {
        return SpecialType::class;
    }

    /**
     * Gets the full name of the current type
     * 
     * @return string 
     */
    public function fullName(): string {
        return $this->fullName;
    }

    /**
     * Checks if the current type is assignable to the provided type
     * 
     * @param ITypeHint $type 
     * 
     * @return bool 
     */
    public function isAssignableTo(ITypeHint $type): bool {
        return $type->isAssignableFrom($this);
    }

    /**
     * Checks if the current type is assignable from the provided type
     * 
     * @param ITypeHint $fromType 
     * 
     * @return bool 
     */
    public function isAssignableFrom(ITypeHint $fromType): bool {
        $fromTypeName = $fromType->fullName();

        $isAssignableCacheKey = $this->fullName . "~" . $fromTypeName;

        return Caching::getWithSet($isAssignableCacheKey, function() use ($fromType, $fromTypeName, $isAssignableCacheKey) {
            if ($this->fullName === $fromTypeName) {
                return true;
            } else if ($this->fullName === "undefined") {
                return true;
            }

            if ($fromType instanceof TypeIntersection) {
                foreach ($fromType->getTypes() as $fromSubTypeName => $fromSubType) {
                    if (!$this->isAssignableFrom($fromSubType)) {
                        return false;
                    }
                }

                return true;
            } else if ($fromType instanceof SpecialType) {
                if ($fromTypeName === "void") {
                    return false;
                } else if ($this->fullName === "mixed") {
                    return true;
                } else if ($this->fullName === "object") {
                    // maybe i should consider enum as an object, not decided yet
                    return in_array($fromTypeName, ["class", "interface"]);
                }
            } else if ($fromType instanceof Type) {
                if ($this->fullName === "void") {
                    return false;
                } else if ($this->fullName === "mixed") {
                    return true;
                } else if ($this->fullName === "object") {
                    return $fromType->isObject();
                } else if ($this->fullName === "class") {
                    return $fromType->isClass();
                } else if ($this->fullName === "interface") {
                    return $fromType->isInterface();
                } else if ($this->fullName === "trait") {
                    return $fromType->isTrait();
                } else if ($this->fullName === "enum") {
                    return $fromType->isEnum();
                } else if ($this->fullName === "resource") {
                    return $fromType->isResource();
                }
            } else if ($fromType instanceof TypeUnion) {
                foreach ($fromType->getTypes() as $fromSubTypeName => $fromSubType) {
                    if ($this->isAssignableFrom($fromSubType)) {
                        return true;
                    }
                }
            } else {
                throw new InvalidArgumentException("Invalid fromType: '" . gettype($fromType) . "' provided.");
            }

            return false;
        }, "ITypeHint::isAssignableFrom");

        // if (isset(static::$isAssignableCache[$isAssignableCacheKey])) {
        //     return static::$isAssignableCache[$isAssignableCacheKey];
        // }

        // if ($this->fullName === $fromTypeName) {
        //     return (self::$isAssignableCache[$isAssignableCacheKey] = true);
        // } else if ($this->fullName === "undefined") {
        //     return (self::$isAssignableCache[$isAssignableCacheKey] = true);
        // }

        // if ($fromType instanceof TypeIntersection) {
        //     foreach ($fromType->getTypes() as $fromSubTypeName => $fromSubType) {
        //         if (!$this->isAssignableFrom($fromSubType)) {
        //             return (self::$isAssignableCache[$isAssignableCacheKey] = false);
        //         }
        //     }

        //     return (self::$isAssignableCache[$isAssignableCacheKey] = true);
        // } else if ($fromType instanceof SpecialType) {
        //     if ($fromTypeName === "void") {
        //         return (self::$isAssignableCache[$isAssignableCacheKey] = false);
        //     } else if ($this->fullName === "mixed") {
        //         return (self::$isAssignableCache[$isAssignableCacheKey] = true);
        //     } else if ($this->fullName === "object") {
        //         // maybe i should consider enum as an object, not decided yet
        //         return (self::$isAssignableCache[$isAssignableCacheKey] = in_array($fromTypeName, ["class", "interface"]));
        //     }
        // } else if ($fromType instanceof Type) {
        //     if ($this->fullName === "void") {
        //         return (self::$isAssignableCache[$isAssignableCacheKey] = false);
        //     } else if ($this->fullName === "mixed") {
        //         return (self::$isAssignableCache[$isAssignableCacheKey] = true);
        //     } else if ($this->fullName === "object") {
        //         return (self::$isAssignableCache[$isAssignableCacheKey] = $fromType->isObject());
        //     } else if ($this->fullName === "class") {
        //         return (self::$isAssignableCache[$isAssignableCacheKey] = $fromType->isClass());
        //     } else if ($this->fullName === "interface") {
        //         return (self::$isAssignableCache[$isAssignableCacheKey] = $fromType->isInterface());
        //     } else if ($this->fullName === "trait") {
        //         return (self::$isAssignableCache[$isAssignableCacheKey] = $fromType->isTrait());
        //     } else if ($this->fullName === "enum") {
        //         return (self::$isAssignableCache[$isAssignableCacheKey] = $fromType->isEnum());
        //     } else if ($this->fullName === "resource") {
        //         return (self::$isAssignableCache[$isAssignableCacheKey] = $fromType->isResource());
        //     }
        // } else if ($fromType instanceof TypeUnion) {
        //     foreach ($fromType->getTypes() as $fromSubTypeName => $fromSubType) {
        //         if ($this->isAssignableFrom($fromSubType)) {
        //             return (self::$isAssignableCache[$isAssignableCacheKey] = true);
        //         }
        //     }
        // } else {
        //     throw new InvalidArgumentException("Invalid fromType: '" . gettype($fromType) . "' provided.");
        // }

        // return (self::$isAssignableCache[$isAssignableCacheKey] = false);
    }

    /**
     * Gets the default value of the current type
     * 
     * @return mixed 
     */
    public function defaultValue() {
        return null;
    }

    /**
     * Gets the string representation of the current type
     * 
     * @return string 
     */
    public function __toString(): string {
        return $this->fullName;
    }

    public function toString(): string {
        return $this->fullName();
    }

    /**
     * Creates a new SpecialType instance
     * 
     * @param string $typeName 
     * 
     * @return SpecialType 
     * 
     * @throws InvalidArgumentException
     */
    public static function new(string $typeName): SpecialType {
        if (empty($typeName = trim($typeName))) {
            return null;
        }
        
        return new self($typeName);
    }

    /**
     * Tries to parse a string into a SpecialType instance
     * 
     * @param string $typeName 
     * 
     * @return SpecialType|null
     */
    public static function tryParseTypeName(string $typeName): ?SpecialType {
        if (empty($typeName = trim($typeName))) {
            return null;
        }

        return Caching::getWithSet($typeName, function() use ($typeName) {
            return isset(self::SPECIAL_TYPES[$typeName]) ? new SpecialType($typeName) : null;
        }, "ITypeHint::create");
    }

    public static function undefined(): SpecialType {
        return new SpecialType("undefined");
    }

    public static function mixed(): SpecialType {
        return new SpecialType("mixed");
    }

    public static function void(): SpecialType {
        return new SpecialType("void");
    }

    public static function object(): SpecialType {
        return new SpecialType("object");
    }

    public static function class(): SpecialType {
        return new SpecialType("class");
    }

    public static function interface(): SpecialType {
        return new SpecialType("interface");
    }

    public static function trait(): SpecialType {
        return new SpecialType("trait");
    }

    public static function enum(): SpecialType {
        return new SpecialType("enum");
    }

    public static function resource(): SpecialType {
        return new SpecialType("resource");
    }
}