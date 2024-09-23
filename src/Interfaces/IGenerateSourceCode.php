<?php

declare(strict_types=1);

namespace Pst\Core\Interfaces;

interface IGenerateSourceCode {
    public function generateSourceCode(...$args): string;
}