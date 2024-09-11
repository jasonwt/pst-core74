<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Exceptions;

use Psr\Container\ContainerExceptionInterface;

/**
 * Represents a container exception.
 * 
 * @package PST\Core\Exceptions
 * 
 * @version 1.0.0
 * 
 * @since 1.0.0
 */
class ContainerException extends DependencyInjectionException implements ContainerExceptionInterface {
    
}