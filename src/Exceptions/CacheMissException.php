<?php

declare(strict_types=1);

namespace Pst\Core\Exceptions;

class CacheMissException extends CoreException {
    public function __construct(string $key) {
        parent::__construct("Cache miss for key: $key");
    }
}