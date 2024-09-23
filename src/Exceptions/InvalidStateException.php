<?php

declare(strict_types=1);

namespace Pst\Core\Exceptions;

class InvalidStateException extends CoreException {
    public function __construct(string $message = 'Invalid state', int $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}