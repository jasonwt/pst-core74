<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;

use Pst\Core\DebugDump\DD;

function dd(... $args): string {
    return DD::dump(...$args);
}

function pd(... $args): string {
    return DD::dump("<pre>", ...[...$args, "</pre>"]);
}