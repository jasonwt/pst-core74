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

class AnsiColorizer extends Colorizer {
    private const ASNI_COLORS = [
        30 => [0   , 0   , 0  ],
        31 => [127 , 0   , 0  ],
        32 => [0   , 127 , 0  ],
        33 => [127 , 127 , 0  ],
        34 => [0   , 0   , 127],
        35 => [127 , 0   , 127],
        36 => [127 , 127 , 0  ],
        37 => [127 , 127 , 127],

        90 => [32  , 32  , 32 ],
        91 => [255 , 0   , 0  ],
        92 => [0   , 255 , 0  ],
        93 => [255 , 255 , 0],
        94 => [0   , 0   , 255],
        95 => [255 , 0   , 255],
        96 => [255 , 255 , 0  ],
        97 => [255 , 255 , 255],
    ];

    public const COLOR_CODES = [
        '30'   => [0.000, 0.000, 0.000], // Black
        '1;30' => [0.000, 0.000, 0.000], // Black
        '2;30' => [0.196, 0.196, 0.196], // Black
        '31'   => [0.804, 0.000, 0.000], // Red
        '1;31' => [1.000, 0.000, 0.000], // Red
        '2;31' => [0.545, 0.000, 0.000], // Red
        '32'   => [0.000, 0.804, 0.000], // Green
        '1;32' => [0.000, 1.000, 0.000], // Green
        '2;32' => [0.000, 0.545, 0.000], // Green
        '33'   => [0.804, 0.804, 0.000], // Yellow
        '1;33' => [1.000, 1.000, 0.000], // Yellow
        '2;33' => [0.545, 0.545, 0.000], // Yellow
        '34'   => [0.000, 0.000, 0.804], // Blue
        '1;34' => [0.000, 0.000, 1.000], // Blue
        '2;34' => [0.000, 0.000, 0.545], // Blue
        '35'   => [0.804, 0.000, 0.804], // Magenta
        '1;35' => [1.000, 0.000, 1.000], // Magenta
        '2;35' => [0.545, 0.000, 0.545], // Magenta
        '36'   => [0.000, 0.804, 0.804], // Cyan
        '1;36' => [0.000, 1.000, 1.000], // Cyan
        '2;36' => [0.000, 0.545, 0.545], // Cyan
        '37'   => [0.898, 0.898, 0.898], // White
        '1;37' => [1.000, 1.000, 1.000], // White
        '2;37' => [0.784, 0.784, 0.784], // White
        '90'   => [0.498, 0.498, 0.498], // Bright Black
        '1;90' => [0.752, 0.752, 0.752], // Bright Black
        '2;90' => [0.392, 0.392, 0.392], // Bright Black
        '91'   => [1.000, 0.333, 0.333], // Bright Red
        '1;91' => [1.000, 0.000, 0.000], // Bright Red
        '2;91' => [0.545, 0.000, 0.000], // Bright Red
        '92'   => [0.333, 1.000, 0.333], // Bright Green
        '1;92' => [0.000, 1.000, 0.000], // Bright Green
        '2;92' => [0.000, 0.545, 0.000], // Bright Green
        '93'   => [1.000, 1.000, 0.333], // Bright Yellow
        '1;93' => [1.000, 1.000, 0.000], // Bright Yellow
        '2;93' => [0.545, 0.545, 0.000], // Bright Yellow
        '94'   => [0.333, 0.333, 1.000], // Bright Blue
        '1;94' => [0.000, 0.000, 1.000], // Bright Blue
        '2;94' => [0.000, 0.000, 0.545], // Bright Blue
        '95'   => [1.000, 0.333, 1.000], // Bright Magenta
        '1;95' => [1.000, 0.000, 1.000], // Bright Magenta
        '2;95' => [0.545, 0.000, 0.545], // Bright Magenta
        '96'   => [0.333, 1.000, 1.000], // Bright Cyan
        '1;96' => [0.000, 1.000, 1.000], // Bright Cyan
        '2;96' => [0.000, 0.545, 0.545], // Bright Cyan
        '97'   => [1.000, 1.000, 1.000], // Bright White
        '1;97' => [1.000, 1.000, 1.000], // Bright White
        '2;97' => [0.784, 0.784, 0.784], // Bright White

        // 0 => ['' => [0.000, 0.000, 0.00]],
        // 1 => ['' => [0.196, 0.196, 0.196]],
        // 2 => ['' => [0.392, 0.392, 0.392]],
        // 3 => ['' => [0.529, 0.529, 0.529]],
        // 4 => ['' => [0.686, 0.686, 0.686]],
        // 5 => ['' => [0.804, 0.804, 0.804]],
        // 6 => ['' => [0.882, 0.882, 0.882]],
        // 7 => ['' => [0.949, 0.949, 0.949]],
        // 8 => ['' => [0.196, 0.196, 0.196]],
        // 9 => ['' => [0.392, 0.392, 0.392]],
        // 10 => ['' => [0.529, 0.529, 0.529]],
        // 11 => ['' => [0.686, 0.686, 0.686]],
        // 12 => ['' => [0.804, 0.804, 0.804]],
        // 13 => ['' => [0.882, 0.882, 0.882]],
        // 14 => ['' => [0.949, 0.949, 0.949]],
        // 15 => ['' => [1.000, 1.000, 1.000]],
        // 16 => ['' => [0.196, 0.196, 0.196]],
        // 17 => ['' => [0.392, 0.392, 0.392]],
        // 18 => ['' => [0.529, 0.529, 0.529]],
        // 19 => ['' => [0.686, 0.686, 0.686]],
        // 20 => ['' => [0.804, 0.804, 0.804]],
        // 21 => ['' => [0.882, 0.882, 0.882]],
        // 22 => ['' => [0.949, 0.949, 0.949]],
        // 23 => ['' => [1.000, 1.000, 1.000]],
        // 24 => ['' => [0.200, 0.200, 0.200]],
        // 25 => ['' => [0.400, 0.400, 0.400]],
        // 26 => ['' => [0.600, 0.600, 0.600]],
        // 27 => ['' => [0.800, 0.800, 0.800]],
        // 28 => ['' => [0.000, 0.000, 0.500]],
        // 29 => ['' => [0.000, 0.000, 1.000]],
        // 30 => ['' => [0.000, 0.000, 0.000], '1;' => [0.000, 0.000, 0.000], '2;' => [0.196, 0.196, 0.196]], // Black
        // 31 => ['' => [0.804, 0.000, 0.000], '1;' => [1.000, 0.000, 0.000], '2;' => [0.545, 0.000, 0.000]], // Red
        // 32 => ['' => [0.000, 0.804, 0.000], '1;' => [0.000, 1.000, 0.000], '2;' => [0.000, 0.545, 0.000]], // Green
        // 33 => ['' => [0.804, 0.804, 0.000], '1;' => [1.000, 1.000, 0.000], '2;' => [0.545, 0.545, 0.000]], // Yellow
        // 34 => ['' => [0.000, 0.000, 0.804], '1;' => [0.000, 0.000, 1.000], '2;' => [0.000, 0.000, 0.545]], // Blue
        // 35 => ['' => [0.804, 0.000, 0.804], '1;' => [1.000, 0.000, 1.000], '2;' => [0.545, 0.000, 0.545]], // Magenta
        // 36 => ['' => [0.000, 0.804, 0.804], '1;' => [0.000, 1.000, 1.000], '2;' => [0.000, 0.545, 0.545]], // Cyan
        // 37 => ['' => [0.898, 0.898, 0.898], '1;' => [1.000, 1.000, 1.000], '2;' => [0.784, 0.784, 0.784]], // White
        // 90 => ['' => [0.498, 0.498, 0.498], '1;' => [0.752, 0.752, 0.752], '2;' => [0.392, 0.392, 0.392]], // Bright Black
        // 91 => ['' => [1.000, 0.333, 0.333], '1;' => [1.000, 0.000, 0.000], '2;' => [0.545, 0.000, 0.000]], // Bright Red
        // 92 => ['' => [0.333, 1.000, 0.333], '1;' => [0.000, 1.000, 0.000], '2;' => [0.000, 0.545, 0.000]], // Bright Green
        // 93 => ['' => [1.000, 1.000, 0.333], '1;' => [1.000, 1.000, 0.000], '2;' => [0.545, 0.545, 0.000]], // Bright Yellow
        // 94 => ['' => [0.333, 0.333, 1.000], '1;' => [0.000, 0.000, 1.000], '2;' => [0.000, 0.000, 0.545]], // Bright Blue
        // 95 => ['' => [1.000, 0.333, 1.000], '1;' => [1.000, 0.000, 1.000], '2;' => [0.545, 0.000, 0.545]], // Bright Magenta
        // 96 => ['' => [0.333, 1.000, 1.000], '1;' => [0.000, 1.000, 1.000], '2;' => [0.000, 0.545, 0.545]], // Bright Cyan
        // 97 => ['' => [1.000, 1.000, 1.000], '1;' => [1.000, 1.000, 1.000], '2;' => [0.784, 0.784, 0.784]], // Bright White
    ];

    private int $escapeCode = 39;
    private string $prefixCode = "";
    
    public static function colorDistance(array $target, array $ansiColor): float {
        // normalize the target values
        // $targetSum = array_sum($target);
        // $ratio = 1.000 / $targetSum;
        // $target = array_map(fn($value) => $value * $ratio, $target);


        $distance = 
            pow((($target[0] - $ansiColor[0])), 2) +
            pow((($target[1] - $ansiColor[1])), 2) +
            pow((($target[2] - $ansiColor[2])), 2);


        return sqrt($distance);

        // return sqrt(
        //     pow($target[0] - $ansiColor[0], 2) +
        //     pow($target[1] - $ansiColor[1], 2) +
        //     pow($target[2] - $ansiColor[2], 2)
        // );
    }

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

        $this->escapeCode = (int) (16 + (36 * round($red*5)) + (6 * round($green*5)) + round($blue*5));
        //$this->escapeCode = (int) (16 + (36 * floor($red * 5)) + (6 * floor($green * 5)) + floor($blue * 5));

        parent::__construct($red, $green, $blue);

        
    }

    

    public function text(string $text): string {
        $textCode = "38;5;" . $this->escapeCode;
        return "\033[{$textCode}m{$text}\033[0m";
    }

    public function back(string $text): string {
        
        
        $backCode = "48;5;" . $this->escapeCode;
        return "\033[{$backCode}m{$text}\033[0m";
    }

    public static function rgb(float $red, float $green, float $blue): self {
        if ($red < 0.000 || $red > 1.000) {
            throw new InvalidArgumentException("Red must be between 0.000 and 1.000.");
        }

        if ($green < 0.000 || $green > 1.000) {
            throw new InvalidArgumentException("Green must be between 0.000 and 1.000.");
        }

        if ($blue < 0.000 || $blue > 1.000) {
            throw new InvalidArgumentException("Blue must be between 0.000 and 1.000.");
        }

        return new self($red, $green, $blue);
    }

    public static function underline(string $text): string {
        return "\033[4m{$text}\033[0m";
    }

    public static function bold(string $text): string {
        return "\033[1m{$text}\033[0m";
    }

    public static function italic(string $text): string {
        return "\033[3m{$text}\033[0m";
    }

    public static function strikethrough(string $text): string {
        return "\033[9m{$text}\033[0m";
    }

    public static function blink(string $text): string {
        return "\033[5m{$text}\033[0m";
    }

    public static function inverse(string $text): string {
        return "\033[7m{$text}\033[0m";
    }

    public static function hidden(string $text): string {
        return "\033[8m{$text}\033[0m";
    }

    public static function reset(string $text): string {
        return "\033[0m{$text}\033[0m";
    }

    public static function resetAll(string $text): string {
        return "\033[0m{$text}\033[0m";
    }

    public static function resetForeground(string $text): string {
        return "\033[39m{$text}\033[0m";
    }

    public static function resetBackground(string $text): string {
        return "\033[49m{$text}\033[0m";
    }

    public static function resetBold(string $text): string {
        return "\033[21m{$text}\033[0m";
    }

    public static function resetItalic(string $text): string {
        return "\033[23m{$text}\033[0m";
    }

    public static function resetUnderline(string $text): string {
        return "\033[24m{$text}\033[0m";
    }

    public static function resetBlink(string $text): string {
        return "\033[25m{$text}\033[0m";
    }

    public static function resetInverse(string $text): string {
        return "\033[27m{$text}\033[0m";
    }

    public static function resetStrikethrough(string $text): string {
        return "\033[29m{$text}\033[0m";
    }

    public static function resetHidden(string $text): string {
        return "\033[28m{$text}\033[0m";
    }


    
}