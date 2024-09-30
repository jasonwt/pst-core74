<?php

declare(strict_types=1);

namespace Pst\Core\Strings\Stylizer;

use Pst\Core\CoreObject;

class AnsiStylizer extends CoreObject implements IStylizer {
    private array $resetStack = [];
    private array $stack = [];
    private int $lastStackIndex = -1;

    public function text(string $text): IStylizer {
        $this->stack[] = $text;

        return $this;
    }

    public function color(float $red, float $green, float $blue, ?string $text = null): IStylizer {
        $color = new AnsiTrueColor($red, $green, $blue);

        $this->stack[] = $color;

        if ($text !== null) {
            $this->stack[] = $text;
        }

        return $this;
    }

    public function __toString(): string {
        return $this->toString();
    }

    public function toString(): string {
        $output = "";

        $renderStack = $this->stack;

        if (count($renderStack) === 0) {
            return "";
        }

        foreach ($renderStack as $k => $style) {
            if ($style instanceof IStyle) {
                $output .= $style->beginStyle();
            } else {
                $output .= $style;
            }
        }

        array_reverse($renderStack);

        do {
            $style = array_shift($renderStack);

            if ($style instanceof IStyle) {
                break;
            }

        } while ($style !== null);

        foreach ($renderStack as $k => $style) {
            if ($style instanceof IStyle) {
                $output .= $style->endStyle();
            }
        }

        $output .= "\033[0m";

        return $output;
        
    }
}