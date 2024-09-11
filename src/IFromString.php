<?php

declare(strict_types=1);

namespace Pst\Core;

interface IFromString {
    public function fromString(string $str): object;
}