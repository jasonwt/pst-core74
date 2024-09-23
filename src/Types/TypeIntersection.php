<?php

declare(strict_types=1);

namespace Pst\Core\Types;

use Pst\Core\Interfaces\ITryParse;
use Pst\Core\CoreObject;

use InvalidArgumentException;

class TypeIntersection extends CoreObject implements ITypeHint, ITryParse {
    private static array $typeNameCache = [];
    private static array $isAssignableCache = [];

    private array $types = [];
    private string $fullName = "";

    /**
     * Creates a new TypeIntersection instance
     * 
     * @param string|ITypeHint ...$types 
     * 
     * @return void 
     * 
     * @throws InvalidArgumentException
     */
    private function __construct(... $types) {
        if (count($types = array_unique($types)) < 2) {
            throw new InvalidArgumentException("At least two unique types must be provided to create a TypeIntersection");
        }

        $this->types = array_reduce($types, function ($acc, $type) {
            if (is_string($type)) {
                if (empty($type = trim($type))) {
                    throw new InvalidArgumentException("Empty type name provided");
                }

                $type = (self::$typeNameCache[$type] ??= TypeHintFactory::new($type));
            }

            if ($type instanceof Type || $type instanceof SpecialType) {
                $acc[(string) $type] ??= $type;
            } else if ($type instanceof TypeIntersection) {
                $acc += $type->getTypes();
            } else if ($type instanceof TypeUnion) {
                $acc[(string) $type] ??= $type;
            } else {
                throw new InvalidArgumentException("Invalid type: '" . gettype($type) . "' provided.");
            }

            return $acc;
        }, []);

        if (isset($this->types["void"]) || isset($this->types["null"])) {
            throw new InvalidArgumentException("undefined, void, mixed and null can not be part of a TypeIntersection");
        }

        // sort by key
        ksort($this->types);

        $this->fullName = implode("&", array_keys($this->types));
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
     * Gets the types in the current TypeIntersection
     * 
     * @return array 
     */
    public function getTypes(): array {
        return $this->types;
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

        if (isset(static::$isAssignableCache[$isAssignableCacheKey])) {
            return static::$isAssignableCache[$isAssignableCacheKey];
        }

        // Default to false since most return values will be false
        self::$isAssignableCache[$isAssignableCacheKey] = false;

        // If the names are the same then the types are the same
        if ($this->fullName === $fromTypeName) {
            return (static::$isAssignableCache[$isAssignableCacheKey] = true);
        }

        if ($fromType instanceof Type) {
            // TypeInsersctions have a minumum of two types so can not be assened from a single type
            return false;

        } else if ( $fromType instanceof SpecialType) {
            // Not sure if I should allow mixed or undefined to be assignable to TypeIntersection
            return false;

        } else if ($fromType instanceof TypeUnion) {
            // TypeUnion can not be assigned to TypeIntersection
            // I could save three instanceof checks by just returning false if !$fromType instanceof TypeIntersection
            // I will keep them for now until I get some of global debuggin flag I can set to disable them
            return false;

        } else if ($fromType instanceof TypeIntersection) {
            if (count($this->types) !== count($fromTypes = $fromType->getTypes())) {
                return false;
            }

            $thisTypes = $this->types;

            // We need to make sure that all types in fromType are assignable to a type in this TypeIntersection
            // without using a single type more than once
            while (count($fromTypes) > 0) {
                $fromSubType = array_pop($fromTypes);

                foreach ($thisTypes as $typeName => $type) {
                    if ($typeName === $fromSubType->fullName() || $type->isAssignableFrom($fromSubType)) {
                        unset($thisTypes[$typeName]);
                        continue 2;
                    }
                }

                return false;
            }

            return (static::$isAssignableCache[$isAssignableCacheKey] = true);
        } else {
            throw new InvalidArgumentException("Invalid fromType: '" . gettype($fromType) . "' provided.");
        }

        return false;
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
    public static function new(... $types): TypeIntersection {
        $types = array_reduce($types, function ($acc, $type) {
            if (is_string($type)) {
                if (empty($type = trim($type))) {
                    throw new InvalidArgumentException("Empty type name provided");
                } else if ($type[0] === "?") {
                    throw new InvalidArgumentException("Null type can not be part of a TypeIntersection");
                }

                $type = (self::$typeNameCache[$type] ??= TypeHintFactory::new($type));
            } else if (!$type instanceof ITypeHint) {
                throw new InvalidArgumentException("Invalid type: '" . gettype($type) . "' provided.");
            }

            $acc[(string) $type] ??= $type;

            return $acc;
        }, []);
        
        return new TypeIntersection(...array_values($types));
    }

    /**
     * Tries to parse a string into a TypeIntersection instance
     * 
     * @param string $type 
     * 
     * @return TypeIntersection|null
     */
    public static function tryParse(string $type): ?TypeIntersection {
        if (empty($type = trim($type))) {
            return null;
        }

        if (isset(self::$typeNameCache[$type])) {
            return self::$typeNameCache[$type];
        }

        if (strpos($type, "?") !== false) {
            // null, void, mixed and undefined can not be part of a TypeIntersection
            return false;
        }

        $types = array_reduce(explode("&", $type), function ($acc, $type) {
            if ($acc === null) {
                return null;
            }

            if (($type = TypeHintFactory::tryParse($type)) === null) {
                return null;
            }

            $acc[(string) $type] ??= $type;

            return $acc;
        }, []);

        if ($types === null || count($types) < 2) {
            return null;
        }

        
        if (isset($types["void"]) || isset($types["null"])) {
            return null;
        }
        

        $typeNameCache[$type] = new TypeIntersection(...array_values($types));
        return $typeNameCache[$typeNameCache[$type]->fullName()] ??= $typeNameCache[$type];
    }
}