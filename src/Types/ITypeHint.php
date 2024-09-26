<?php

declare(strict_types=1);

namespace Pst\Core\Types;

use Pst\Core\Interfaces\ICoreObject;

interface ITypeHint extends ICoreObject {
    public function fullName(): string;
    
    public function isAssignableTo(ITypeHint $type): bool;
    public function isAssignableFrom(ITypeHint $type): bool;

    public function defaultValue();

    public function __toString(): string;
}