<?php

declare(strict_types=1);

namespace Pst\Core;

interface IGenerateSourceCode {
    public function generateSourceCode(...$args): string;
}