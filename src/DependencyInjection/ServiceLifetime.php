<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\DependencyInjection;

use Pst\Core\Enum;
use Pst\Core\Interfaces\IEnum;

/**
 * Represents a service lifetime enum.
 * 
 * @package PST\DependencyInjection
 * 
 * @version 1.0.0
 * 
 * @since 1.0.0
 */
class ServiceLifetime extends Enum implements IEnum {
    public static function cases(): array {
        return ["Transient" => "Transient", "Singleton" => "Singleton"];
    }

    public static function Transient(): ServiceLifetime {
        return new ServiceLifetime("Transient");
    }

    public static function Singleton(): ServiceLifetime {
        return new ServiceLifetime("Singleton");
    }
}