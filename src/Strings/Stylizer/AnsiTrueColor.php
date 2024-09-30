<?php

declare(strict_types=1);

namespace Pst\Core\Strings\Stylizer;

use Pst\Core\CoreObject;

use InvalidArgumentException;


class AnsiTrueColor extends CoreObject implements IColor {
    private array $rgb = [1,1,1];

    public function __construct(float $red, float $green, float $blue) {
        if ($red < 0.000 || $red > 1.000) {
            throw new InvalidArgumentException("Red must be between 0.000 and 1.000.");
        }

        if ($green < 0.000 || $green > 1.000) {
            throw new InvalidArgumentException("Green must be between 0.000 and 1.000.");
        }

        if ($blue < 0.000 || $blue > 1.000) {
            throw new InvalidArgumentException("Blue must be between 0.000 and 1.000.");
        }

        $this->rgb = [(int) round($red * 255), (int) round($green * 255), (int) round($blue * 255)];
    }

    public function beginStyle(): string {
        return "\033[38;2;" . $this->rgb[0] . ";" . $this->rgb[1] . ";" . $this->rgb[2] . "m";
    }

    public function endStyle(): string {
        return "\033[0m";
    }
}