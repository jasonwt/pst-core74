<?php

declare(strict_types=1);

namespace Pst\Core\Collections\Traits;

use Pst\Core\Types\ITypeHint;
use Pst\Core\Types\TypeHint;

trait EnumerableTrait {
    public function T(): ITypeHint {
        return TypeHint::fromTypeNames("mixed");
    }
}