<?php

declare(strict_types=1);

namespace Pst\Core\Interfaces;

interface IFromString {
    public function fromString(string $str): object;
}