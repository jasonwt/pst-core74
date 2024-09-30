<?php
declare(strict_types=1);

namespace Pst\Core\Strings;

use InvalidArgumentException;
use Pst\Core\CoreObject;

class AnsiStylizer extends CoreObject implements IStyleizer {
    private array $styleStack = [];

    private function computeColorCode(float $red, float $green, float $blue): int {
        if ($red < 0.000 || $red > 1.000) {
            throw new InvalidArgumentException("Red must be between 0.000 and 1.000.");
        }

        if ($green < 0.000 || $green > 1.000) {
            throw new InvalidArgumentException("Green must be between 0.000 and 1.000.");
        }

        if ($blue < 0.000 || $blue > 1.000) {
            throw new InvalidArgumentException("Blue must be between 0.000 and 1.000.");
        }

        return (int) (16 + (36 * round($red*5)) + (6 * round($green*5)) + round($blue*5));
    }

    public function color(string $input, float $red, float $green, float $blue): string {
        $colorCode = $this->computeColorCode($red, $green, $blue);
        $this->styleStack[] = "\033[38;5;${colorCode}m";

        return "\033[38;5;${colorCode}m$input\033[0m";
    }

    public function bg(string $input, float $red, float $green, float $blue): string {
        $colorCode = $this->computeColorCode($red, $green, $blue);
        $this->styleStack[] = "\033[48;5;${colorCode}m";

        return "\033[48;5;${colorCode}m$input\033[0m";
    }

    public function bold(string $input): string {
        $this->styleStack[] = "\033[1m";

        return "\033[1m$input\033[0m";
    }

    public function italic(string $input): string {
        $this->styleStack[] = "\033[3m";

        return "\033[3m$input\033[0m";
    }

    public function underline(string $input): string {
        $this->styleStack[] = "\033[4m";

        return "\033[4m$input\033[0m";
    }

    public function strike(string $input): string {
        $this->styleStack[] = "\033[9m";

        return "\033[9m$input\033[0m";

    }

    public function blink(string $input): string {
        $this->styleStack[] = "\033[5m";

        return "\033[5m$input\033[0m";
    }

    public function inverse(string $input): string {
        $this->styleStack[] = "\033[7m";

        return "\033[7m$input\033[0m";
    }
}

/*

$output = $styleizer->color



*/