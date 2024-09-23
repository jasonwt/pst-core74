<?php

declare(strict_types=1);

namespace Pst\Core\Collections;

interface IToCollection {
    public function toCollection(): ICollection;
}