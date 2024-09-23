<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;

use Pst\Core\CoreObjectTrait;
use Pst\Core\Interfaces\ICoreObject;

/**
 * Represents a core object.
 * 
 * @package PST\Core
 * 
 * @version 1.0.0
 * 
 * @since 1.0.0
 * 
 * @see CoreObjectTrait
 */
abstract class CoreObject implements ICoreObject {
    use CoreObjectTrait;
}