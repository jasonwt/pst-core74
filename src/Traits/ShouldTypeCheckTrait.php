<?php

declare(strict_types=1);

namespace Pst\Core\Traits;

trait ShouldTypeCheckTrait {
    private static bool $shouldTypeCheck = true;

    public static function shouldTypeCheck(): bool {
        return self::$shouldTypeCheck;
    }

    public static function enableTypeChecking(): void {
        self::$shouldTypeCheck = true;
    }

    public static function disableTypeChecking(): void {
        self::$shouldTypeCheck = false;
    }
}