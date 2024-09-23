<?php

declare(strict_types=1);

namespace Pst\Core\Interfaces;

interface IConstructFromString {
    public static function tryConstructFromString(string $input): ?self;
}