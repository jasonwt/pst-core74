<?php

declare(strict_types=1);

namespace Pst\Core\RichTextBuilder;

use Pst\Core\Enum;

class RichTextColor extends Enum {
    public static function cases(): array {
        return [
            'BLACK' => '31|40',
            'RED' => '31|41',
            'GREEN' => '32|42',
            'YELLOW' => '33|43',
            'BLUE' => '34|44',
            'MAGENTA' => '35|45',
            'CYAN' => '36|46',
            'WHITE' => '37|47',
            'BRIGHT_BLACK' => '90|100',
            'BRIGHT_RED' => '91|101',
            'BRIGHT_GREEN' => '92|102',
            'BRIGHT_YELLOW' => '93|103',
            'BRIGHT_BLUE' => '94|104',
            'BRIGHT_MAGENTA' => '95|105',
            'BRIGHT_CYAN' => '96|106',
            'BRIGHT_WHITE' => '97|107',
            'DEFAULT' => '39|49',
        ];
    }

    public static function BLACK(): self {
        return new self('31|40');
    }

    public static function RED(): self {
        return new self('31|41');
    }

    public static function GREEN(): self {
        return new self('32|42');
    }

    public static function YELLOW(): self {
        return new self('33|43');
    }

    public static function BLUE(): self {
        return new self('34|44');
    }

    public static function MAGENTA(): self {
        return new self('35|45');
    }

    public static function CYAN(): self {
        return new self('36|46');
    }

    public static function WHITE(): self {
        return new self('37|47');
    }

    public static function BRIGHT_BLACK(): self {
        return new self('90|100');
    }

    public static function BRIGHT_RED(): self {
        return new self('91|101');
    }

    public static function BRIGHT_GREEN(): self {
        return new self('92|102');
    }

    public static function BRIGHT_YELLOW(): self {
        return new self('93|103');
    }

    public static function BRIGHT_BLUE(): self {
        return new self('94|104');
    }

    public static function BRIGHT_MAGENTA(): self {
        return new self('95|105');
    }

    public static function BRIGHT_CYAN(): self {
        return new self('96|106');
    }

    public static function BRIGHT_WHITE(): self {
        return new self('97|107');
    }

    public static function DEFAULT(): self {
        return new self('39|49');
    }

    public function beginText(): string {
        return "\033[" . explode("|", $this->value())[0] . "m";
    }

    public function endText(): string {
        return "\033[0m";
    }

    public function text(string $text): string {
        return $this->beginText() . $text . $this->endText();
    }

    public function beginBackground(): string {
        return "\033[" . explode("|", $this->value())[1] . "m";
    }

    public function endBackground(): string {
        return "\033[0m";
    }

    public function background(string $text): string {
        return $this->beginBackground() . $text . $this->endBackground();
    }
}