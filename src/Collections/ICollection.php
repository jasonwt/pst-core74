<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Collections;

use ArrayAccess;

interface ICollection extends IReadOnlyCollection, ArrayAccess {
    public function add($item, $key = null);
    public function clear();
    public function remove($key): bool;
}