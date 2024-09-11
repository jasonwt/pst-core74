<?php

declare(strict_types=1);

namespace Pst\Core\Exceptions;

use Pst\Core\Types\ITypeHint;

use Throwable;
use InvalidArgumentException;

class IsAssignableExceptionFrom extends InvalidArgumentException {
    private ITypeHint $from;
    private ITypeHint $to;

    public function __construct(ITypeHint $to, ITypeHint $from, string $message = "", int $code = 0, Throwable $previous = null) {
        $message = trim(trim($message) . " " . get_class($to) . "::" . $to->fullName() . "->isAssignableFrom(" . get_class($from) . "::" . $from->fullName() . ") failed.");

        parent::__construct($message, $code, $previous);
    }

    public function getFrom(): ITypeHint {
        return $this->from;
    }

    public function getTo(): ITypeHint {
        return $this->to;
    }
}