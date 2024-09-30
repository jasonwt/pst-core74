<?php

declare(strict_types=1);

namespace Pst\Core\Strings\Stylizer;

use Pst\Core\Interfaces\ICoreObject;

interface IStyle extends ICoreObject {
    public function beginStyle(): string;
    public function endStyle(): string;
}