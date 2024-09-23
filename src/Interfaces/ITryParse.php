<?php

declare(strict_types=1);

namespace Pst\Core\Interfaces;

interface ITryParse {
    public static function tryParse(string $input);
}