<?php

declare(strict_types=1);

namespace Pst\Core;

interface IConstructFromString {
    public static function tryConstructFromString(string $input): ?self;
}