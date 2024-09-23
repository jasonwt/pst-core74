<?php

declare(strict_types=1);

namespace Pst\Core\Traits;

use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Collections\IReadonlyCollection;
use Pst\Core\Collections\ReadonlyCollection;

trait aStaticErrorsTrait {
    private static array $staticErrorsTraitErrors = [];

    protected static function clearStaticErrors(?string $functionName = null) {
        $functionName ??= debug_backtrace()[1]['function'];
        static::$staticErrorsTraitErrors[$functionName] = [];
    }

    protected static function addStaticErrors(array $keyMessages, ?string $functionName = null, bool $clearExistingErrors = true) {
        $functionName ??= debug_backtrace()[1]['function'];

        if ($clearExistingErrors) {
            static::clearStaticErrors($functionName);
        }
        
        if (array_keys($keyMessages) === range(0, count($keyMessages) - 1)) {
            foreach ($keyMessages as $errorMessage) {
                static::addStaticError($errorMessage, null, $functionName);
            }
        } else {
            foreach ($keyMessages as $key => $errorMessage) {
                static::addStaticError($errorMessage, $key, $functionName);
            }
        }
    }

    protected static function addStaticError(string $errorMessage, ?string $key = null, ?string $functionName = null) {
        $functionName ??= debug_backtrace()[1]['function'];

        if ($key !== null) {
            static::$staticErrorsTraitErrors[$functionName][$key] = $errorMessage;
        } else {
            static::$staticErrorsTraitErrors[$functionName][] = $errorMessage;
        }
    }

    public static function getStaticErrors(): IReadonlyCollection {
        return ReadonlyCollection::new(static::$staticErrorsTraitErrors ?? [], TypeHintFactory::array());
    }
}