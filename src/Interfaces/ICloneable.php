<?php

declare(strict_types=1);

namespace Pst\Core\Interfaces;

interface ICloneable {
    public function clone(): self;
}