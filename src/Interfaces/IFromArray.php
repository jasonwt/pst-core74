<?php

declare(strict_types=1);

namespace Pst\Core\Interfaces;

interface IFromArray {
    public static function fromArray(array $arr): object;
}