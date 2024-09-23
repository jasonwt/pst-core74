<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Interfaces;


interface IEqualityComparer {
    public function equals($x, $y): bool;
}