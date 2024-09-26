<?php

declare(strict_types=1);

namespace Pst\Core\Enumerable\Linq;

use Pst\Core\CoreObject;

use Closure;

use InvalidArgumentException;

class Selectors extends CoreObject implements ISelectors {
    private ?Closure $valueSelector = null;
    private ?Closure $keySelector = null;

    public function __construct(?Closure $valueSelector = null, ?Closure $keySelector = null) {
        if ($valueSelector === null && $keySelector === null) {
            throw new InvalidArgumentException('At least one selector must be provided.');
        }

        $this->valueSelector = $valueSelector;
        $this->keySelector = $keySelector;
    }

    public function valueSelector(): ?Closure {
        return $this->valueSelector;
    }

    public function keySelector(): ?Closure {
        return $this->keySelector;
    }

    public static function create(?Closure $valueSelector = null, ?Closure $keySelector = null): Selectors {
        return new static($valueSelector, $keySelector);
    }
}