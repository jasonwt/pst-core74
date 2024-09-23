<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;

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
trait CoreObjectTrait {
    private static ?string $objectNamespace = null;
    private static ?string $objectClassName = null;

    private array $coreObjectTraitCache = [];
    private ?int $coreObjectTraitHashCode = null;

    public static function getNamespace(): string {
        return static::$objectNamespace ??= implode('\\', array_slice(explode('\\', static::class), 0, -1));
    }

    public static function getClassName(): string {
        return static::$objectClassName ??= array_slice(explode('\\', static::class), -1)[0];
    }

    public function getObjectId(): int {
        return spl_object_id($this);
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
        return ($this->coreObjectTraitHashCode ??= hexdec(substr(hash("sha256", serialize($this)), 0, PHP_INT_SIZE * 2)));
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