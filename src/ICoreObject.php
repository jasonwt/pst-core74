<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;

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
interface ICoreObject extends IToString {
    public function getNamespace(): string;
    public function getClassName(): string;
    
    public function getType(): Type;
    public function getHashCode(): int;

    public function __toString(): string;
}