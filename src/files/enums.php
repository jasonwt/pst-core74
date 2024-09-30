<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;

if (!function_exists("enum_exists")) {
    function enum_exists(string $name): bool {
        return is_a($name, Enum::class, true);
    }
}

function is_enum($value): bool {
    if (!is_object($value)) {
        return false;
    }

    return is_a($value, Enum::class, true) || enum_exists(get_class($value));
}