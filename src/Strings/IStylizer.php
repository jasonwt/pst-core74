<?php

declare(strict_types=1);

namespace Pst\Core\Strings;

use Pst\Core\Interfaces\ICoreObject;

interface IStyleizer extends ICoreObject {
    public function color(string $input, float $red, float $green, float $blue): string;
    public function bg(string $input, float $red, float $green, float $blue): string;
    public function bold(string $input): string;
    public function italic(string $input): string;
    public function underline(string $input): string;
    public function strike(string $input): string;
    public function blink(string $input): string;
    public function inverse(string $input): string;
}