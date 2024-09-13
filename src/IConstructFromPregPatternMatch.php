<?php

declare(strict_types=1);

namespace Pst\Core;

interface IConstructFromPregMatch {
    public static function getPregMatchPattern(): string;

    public static function tryConstructFromPregMatch(array $matches): ?self;
}