<?php

declare(strict_types=1);

namespace Pst\Core\Collections;

interface IToReadonlyCollection {
    public function toReadonlyCollection(): IReadonlyCollection;
}