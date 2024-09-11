<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Traits;

use Pst\Core\Types\Type;

/**
 * A trait that provides common functionality for objects.
 * 
 * @package PST\Core\Traits
 * 
 * @version 1.0.0
 * 
 * @since 1.0.0
 * 
 * @see CoreObject
 */
trait CoreObjectTraits {
    private array $coreObjectTraitCache = [];
    private ?int $CoreObjectTrait_hashCode = null;

    public function getNamespace(): string {
        if (isset($this->coreObjectTraitCache[__FUNCTION__])) {
            return $this->coreObjectTraitCache[__FUNCTION__];
        }

        $classNameParts = explode('\\', static::class);
        array_pop($classNameParts);
        
        return $this->coreObjectTraitCache[__FUNCTION__] = implode('\\', $classNameParts);
    }

    public function getClassName(): string {
        if (isset($this->coreObjectTraitCache[__FUNCTION__])) {
            return $this->coreObjectTraitCache[__FUNCTION__];
        }

        $classNameParts = explode('\\', static::class);
        return $this->coreObjectTraitCache[__FUNCTION__] = end($classNameParts);
    }

    /**
     * Gets the type of the object.
     * 
     * @return Type The type of the object.
     */
    public function getType(): Type {
        return Type::typeOf(static::class);
    }

    /**
     * Gets the hash code of the object.
     * 
     * @return int The hash code of the object.
     */
    public function getHashCode(): int {
        return ($this->CoreObjectTrait_hashCode ??= hexdec(substr(hash("sha256", serialize($this)), 0, PHP_INT_SIZE * 2)));
    }

    /**
     * Gets the string representation of the object.
     * 
     * @return string 
     */
    public function __toString(): string {
        return print_r($this, true);
    }

    public function toString(): string {
        return $this->__toString();
    }
}