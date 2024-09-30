<?php

declare(strict_types=1);

namespace Pst\Core\Interfaces;

interface IShouldTypeCheck {
    public static function shouldTypeCheck(): bool;
    public static function enableTypeChecking(): void;
    public static function disableTypeChecking(): void;
}