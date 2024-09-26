<?php

declare(strict_types=1);

namespace Pst\Core\Enumerable\Linq;

use Pst\Core\Interfaces\ICoreObject;

use Closure;

interface ISelectors extends ICoreObject {
    public function valueSelector(): ?Closure;
    public function keySelector(): ?Closure;
}