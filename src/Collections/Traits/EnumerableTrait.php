<?php

declare(strict_types=1);

namespace Pst\Core\Collections\Traits;

use Pst\Core\Types\ITypeHint;
use Pst\Core\Types\TypeHintFactory;

trait EnumerableTrait {
    public function T(): ITypeHint {
        return TypeHintFactory::tryParse("mixed");
    }
}