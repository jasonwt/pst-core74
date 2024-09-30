<?php

declare(strict_types=1);

namespace Pst\Core;

use Pst\Core\Types\ITypeHint;

use Pst\Core\Types\TypedClosureTrait;

use Closure;
use InvalidArgumentException;
use Pst\Core\Types\TypeHintFactory;

/**
 * Represents a type checking function using a Closure.
 * 
 * @package PST\Core
 * 
 * @version 1.0.0
 * 
 * @since 1.0.0
 * 
 * @see TypedClosureTrait
 */
final class Func {
    use TypedClosureTrait {
        __construct as private __TypedClosureTrait__construct;
        getMinRequiredParameters as public;
        getClosure as public;
        getParameterNames as public;
        getReturnTypeHint as public;
        getParameterTypeHints as public;
        getParameterTypeHint as public;
        willValidateParameters as public;
        willValidateReturnType as public;
    }

    // I am setting this to private because I want an opertunity to monitor a global "Build Type" property that can 
    // return just the closure when in Release mode for performance reasons
    private function __construct(Closure $closure, ?ITypeHint ...$tClosureParameterTypeHints) {
        if (count($tClosureParameterTypeHints) === 0) {
            throw new InvalidArgumentException("At least one type hint must be specified for the required return type.");
        }

        $returnTypeHint = array_pop($tClosureParameterTypeHints) ?? TypeHintFactory::undefined();

        $this->__TypedClosureTrait__construct($closure, $returnTypeHint, ...$tClosureParameterTypeHints);

        if ($this->validReturnType == "void") {
            throw new InvalidArgumentException("A Func can not have a void return type.");
        }
    }

    // I would much rather return null|Closure|Func here but php 7.4 doesn't support union types
    public static function new(Closure $closure, ?ITypeHint ...$tClosureParameterTypeHints) {
        return new self($closure, ...$tClosureParameterTypeHints);
    }
}