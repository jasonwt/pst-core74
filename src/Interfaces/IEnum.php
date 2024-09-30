<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Interfaces;

interface IEnum extends ICoreObject, IToString {
    public static function cases(): array;

    public function value();
    public function name();

    public static function tryFromName(string $name): ?IEnum;
    public static function fromName(string $name): IEnum;

    public static function tryFrom($enumValue): ?IEnum;
    public static function from($enumValue): IEnum;

    public static function getPregMatchPattern(string $delimiter = "/"): string;
}