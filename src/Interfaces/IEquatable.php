<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Interfaces;

/**
 * Interface IEquatable
 * 
 * @package PST\Core
 * @version 1.0.0
 * @since 1.0.0
 */
interface IEquatable {
    public function compareTo($other): bool;
}