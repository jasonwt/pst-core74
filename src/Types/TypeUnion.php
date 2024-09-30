<?php

declare(strict_types=1);

namespace Pst\Core\Types;

use Pst\Core\CoreObject;
use Pst\Core\Caching\Caching;

use InvalidArgumentException;

class TypeUnion extends CoreObject implements ITypeHint {
    private array $types = [];
    private string $fullName = "";

    /**
     * Creates a new TypeUnion instance
     * 
     * @param string|ITypeHint ...$types 
     * 
     * @return void 
     * 
     * @throws InvalidArgumentException
     */
    private function __construct(... $types) {
        if (count($types = array_unique($types)) < 2) {
            throw new InvalidArgumentException("At least two unique types must be provided to create a TypeUnion");
        }

        $this->types = array_reduce($types, function ($acc, $type) {
            if (is_string($type)) {
                if (empty($type = trim($type))) {
                    throw new InvalidArgumentException("Empty type name provided");
                }

                $type = TypeHintFactory::tryParseTypeName($type);
            }

            if ($type instanceof Type || $type instanceof SpecialType) {
                $acc[(string) $type] ??= $type;
            } else if ($type instanceof TypeUnion) {
                $acc += $type->getTypes();
            } else if ($type instanceof TypeIntersection) {
                $acc[(string) $type] ??= $type;
            } else {
                throw new InvalidArgumentException("Invalid type: '" . gettype($type) . "' provided.");
            }

            return $acc;
        }, []);

        // sort by key
        ksort($this->types);

        $this->fullName = implode("|", array_keys($this->types));
    }

    public function typeGroup(): string {
        return TypeUnion::class;
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
     * Gets the types of the current instance
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

        return Caching::getWithSet($isAssignableCacheKey, function() use ($fromType, $fromTypeName, $isAssignableCacheKey) {
            if ($this->fullName === $fromTypeName) {
                return true;
            } else if ($fromType instanceof TypeIntersection) {
                foreach ($fromType->getTypes() as $fromSubTypeName => $fromSubType) {
                    if (!$this->isAssignableFrom($fromSubType)) {
                        return false;
                    }
                }

                return true;
            } else if ($fromType instanceof SpecialType || $fromType instanceof Type) {
                foreach ($this->types as $typeName => $type) {
                    if ($typeName === $fromTypeName || $type->isAssignableFrom($fromType)) {
                        return true;
                    }
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
    }

    /**
     * Gets the default value of the current instance
     * 
     * @return mixed 
     */
    public function defaultValue() {
        return null;
    }

    /**
     * Gets the string representation of the current instance
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
     * Creates a create TypeUnion instance
     * 
     * @param string|ITypeHint ...$types 
     * 
     * @return TypeUnion
     * 
     * @throws InvalidArgumentException
     */
    public static function create(... $types): TypeUnion {
        $types = array_reduce($types, function ($acc, $type) {
            if (is_string($type)) {
                if (empty($type = trim($type))) {
                    throw new InvalidArgumentException("Empty type name provided");
                }
                
                if ($type[0] === "?") {
                    $acc["null"] ??= TypeHintFactory::new("null");
                    $type = substr($type, 1);
                }

                $type = TypeHintFactory::new($type);
            } else if (!$type instanceof ITypeHint) {
                throw new InvalidArgumentException("Invalid type: '" . gettype($type) . "' provided.");
            }

            $acc[(string) $type] ??= $type;

            return $acc;
        }, []);

        return new TypeUnion(...array_values($types));
    }

    /**
     * Tries to parse a string into a TypeUnion instance
     * 
     * @param string $type 
     * 
     * @return TypeUnion|null
     */
    public static function tryParseTypeName(string $typeName): ?TypeUnion {
        if (empty($type = trim($typeName))) {
            return null;
        }

        return Caching::getWithSet($type, function() use ($type) {
            $types = array_reduce(explode("|", str_replace("?", "null|", $type)), function ($acc, $type) {
                if ($acc === null) {
                    return null;
                } else if (($type = TypeHintFactory::tryParseTypeName($type)) === null) {
                    return null;
                }

                $acc[(string) $type] ??= $type;

                return $acc;
            }, []);

            if ($type === null || count($types) < 2) {
                return null;
            }

            return new TypeUnion(...array_values($types));
        }, "ITypeHint::create");
    }
}