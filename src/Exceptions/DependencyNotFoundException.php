<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Represents a dependency not found exception.
 * 
 * @package PST\DependencyInjection\Exceptions
 * 
 * @version 1.0.0
 * 
 * @since 1.0.0
 */
class DependencyNotFoundException extends DependencyInjectionException implements NotFoundExceptionInterface {
    
}