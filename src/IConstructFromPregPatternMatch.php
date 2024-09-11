<?php

declare(strict_types=1);

namespace Pst\Core;

interface IConstructFromPregPatternMatch {
    public static function getPregMatchPattern(): string;

    public static function tryConstructFromPregPatternMatch(array $matches): ?self;
}