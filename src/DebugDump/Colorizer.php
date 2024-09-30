<?php

declare(strict_types=1);

namespace Pst\Core\DebugDump;

use Exception;
use InvalidArgumentException;
use Pst\Core\CoreObject;
use Pst\Core\Enumerable\Linq\Linq;
use Pst\Core\Interfaces\IToString;
use Pst\Core\RichTextBuilder\RichTextColor;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Traversable;

class Colorizer extends CoreObject {
    private float $red;
    private float $green;
    private float $blue;

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
        
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }
}