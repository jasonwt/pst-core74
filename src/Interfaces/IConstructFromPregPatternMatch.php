<?php

declare(strict_types=1);

namespace Pst\Core\Interfaces;

interface IConstructFromPregMatch {
    public static function getPregMatchPattern(): string;

    public static function tryConstructFromPregMatch(array $matches): ?self;
}