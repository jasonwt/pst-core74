<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;

interface IComparer {
    public function compare($x, $y): int;
}