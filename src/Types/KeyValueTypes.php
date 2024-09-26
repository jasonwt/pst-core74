<?php

declare(strict_types=1);

namespace Pst\Core\Types;

use InvalidArgumentException;
use Pst\Core\CoreObject;
use Pst\Core\Interfaces\ICoreObject;
use Pst\Core\Types\ITypeHint;

class KeyValueTypes extends CoreObject implements ICoreObject {
    private ?ITypeHint $T = null;
    private ?ITypeHint $TKey = null;
    
    public function __construct(ITypeHint $T = null, ITypeHint $TKey = null) {
        if ($T === null && $TKey === null) {
            throw new InvalidArgumentException('At least one type hint must be provided.');
        }

        $this->T = $T;
        $this->TKey = $TKey;
    }

    public function T(): ?ITypeHint {
        return $this->T;
    }

    public function TKey(): ?ITypeHint {
        return $this->TKey;
    }

    public static function create(ITypeHint $T = null, ITypeHint $TKey = null): KeyValueTypes {
        return new static($T, $TKey);
    }
}