<?php

declare(strict_types=1);

namespace Pst\Core\Types;

use Pst\Core\CoreObject;

class TypedClosureOptionalParameter extends CoreObject implements ITypeHint {
    private ITypeHint $typeHint;

    public function __construct(ITypeHint $typeHint) {
        $this->typeHint = $typeHint;
    }

    public function typeGroup(): string {
        return $this->typeHint->typeGroup();
    }

    public function fullName(): string {
        return $this->typeHint->fullName();
    }

    public function isAssignableFrom(ITypeHint $type): bool {
        return $type->isAssignableFrom($this->typeHint);
    }

    public function isAssignableTo(ITypeHint $type): bool {
        return $type->isAssignableTo($this->typeHint);
    }

    public function defaultValue() {
        return $this->typeHint->defaultValue();
    }

    public function toString(): string {
        return $this->typeHint->toString();
    }

    public function __toString(): string {
        return $this->typeHint->__toString();
    }
}