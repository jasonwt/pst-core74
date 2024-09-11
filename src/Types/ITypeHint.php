<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Types;

interface ITypeHint {
    public function isAssignableFrom(ITypeHint $typeHint): bool;
    public function isAssignableTo(ITypeHint $typeHint): bool;
    public function fullName(): string;
    public function __toString(): string;
    
}