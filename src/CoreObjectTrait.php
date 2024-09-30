<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;

use Pst\Core\Caching\Caching;
use Pst\Core\Interfaces\IToString;
use Pst\Core\Types\Type;
use ReflectionProperty;

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
    
    protected array $coreObjectCache = [];

    private ?int $coreObjectTraitHashCode = null;
    
    public static function classNamespace(): string {
        return Caching::getWithSet(
            static::class . ":" . __FUNCTION__, 
            function() {
                return implode('\\', array_slice(explode('\\', static::class), 0, -1));
            }
        );
        
    }

    public static function className(): string {
        return Caching::getWithSet(
            static::class . ":" . __FUNCTION__, 
            function() {
                return array_slice(explode('\\', static::class), -1)[0];
            }
        );
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
        return Caching::getWithSet(
            static::class . ":" . __FUNCTION__, 
            function() {
                return hexdec(substr(hash("sha256", serialize($this)), 0, PHP_INT_SIZE * 2));
            }
        );
    }
}