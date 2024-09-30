<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Interfaces;

use Pst\Core\Types\Type;

/**
 * Represents a core object.
 * 
 * @package PST\Core
 * 
 * @version 1.0.0
 * 
 * @since 1.0.0
 */
interface ICoreObject {
    public static function classNamespace(): string;
    public static function className(): string;
    
    public function getObjectId(): int;
    public function getType(): Type;
    public function getHashCode(): int;
}