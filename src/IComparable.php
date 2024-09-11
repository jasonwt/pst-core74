<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;

/**
 * Interface IConvertable
 * 
 * @package PST\Core
 * @version 1.0.0
 * @since 1.0.0
 */
interface IConvertable {
    public function compareTo(?object $other): int;
}