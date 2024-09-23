<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Interfaces;

use Pst\Core\Types\Type;

/**
 * Interface IConvertible
 * 
 * @package PST\Core
 * 
 * @version 1.0.0
 * 
 * @since 1.0.0
 * 
 * @see Convert
 */
interface IConvertible {
    public function toBoolean(): bool;
    public function toInteger(): int;
    public function toFloat(): float;
    public function toString(): string;
    public function toType(Type $type);
}

