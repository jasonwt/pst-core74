<?php

declare(strict_types=1);

namespace Pst\Core\Types;

use Pst\Core\Caching\Caching;
use Pst\Core\Caching\NonEvictingArrayCache;
use Pst\Core\Interfaces\ICoreObject;
use Pst\Core\Interfaces\IToString;

interface ITypeHint extends ICoreObject, IToString {
    public function fullName(): string;
    public function typeGroup(): string;
    
    public function isAssignableTo(ITypeHint $type): bool;
    public function isAssignableFrom(ITypeHint $type): bool;

    public function defaultValue();

    public function __toString(): string;
}

Caching::registerCache("ITypeHint::create", new NonEvictingArrayCache());
Caching::registerCache("ITypeHint::isAssignableFrom", new NonEvictingArrayCache());