<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;


interface IEqualityComparer {
    public function equals($x, $y): bool;
}