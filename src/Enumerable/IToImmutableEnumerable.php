<?php

declare(strict_types=1);

namespace Pst\Core\Enumerable;

interface IToImmutableEnumerable {
    public function toImmutableEnumerable(): IImmutableEnumerable;
}