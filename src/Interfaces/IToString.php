<?php

declare(strict_types=1);

namespace Pst\Core\Interfaces;

interface IToString {
    public function __toString(): string;
    public function toString(): string;
}