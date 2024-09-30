<?php

declare(strict_types=1);

namespace Pst\Core\Strings\Stylizer;

use Pst\Core\Interfaces\IToString;
use Pst\Core\Interfaces\ICoreObject;


interface IStylizer extends ICoreObject, IToString {
    public function text(string $text): IStylizer;
    public function color(float $red, float $green, float $blue, ?string $text = null): IStylizer;
}