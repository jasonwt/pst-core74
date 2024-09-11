<?php

declare(strict_types=1);

namespace Pst\Core;

interface ITryParse {
    public static function tryParse(string $input);
}