<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;

use Pst\Core\Traits\CoreObjectTraits;

/**
 * Represents a core object.
 * 
 * @package PST\Core
 * 
 * @version 1.0.0
 * 
 * @since 1.0.0
 * 
 * @see CoreObjectTraits
 */
abstract class CoreObject implements ICoreObject {
    use CoreObjectTraits;
}