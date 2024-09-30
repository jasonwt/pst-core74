<?php

declare(strict_types=1);

namespace Pst\Core\Traits;

use Pst\Core\Enumerable\Enumerable;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Collections\IReadonlyCollection;
use Pst\Core\Collections\ReadonlyCollection;

trait aErrorsTrait {
    private array $errorsTraitErrors = [];

    protected function clearErrors(?string $functionName = null) {
        if ($functionName === null) {
            $this->errorsTraitErrors = [];
        } else {
            $this->errorsTraitErrors[$functionName] = [];
        }
    }

    protected function addErrors(array $keyMessages, ?string $functionName) {
        $functionName ??= debug_backtrace()[1]['function'];
        
        if (array_keys($keyMessages) === range(0, count($keyMessages) - 1)) {
            foreach ($keyMessages as $errorMessage) {
                $this->addError($errorMessage, null, $functionName);
            }
        } else {
            foreach ($keyMessages as $key => $errorMessage) {
                $this->addError($errorMessage, $key, $functionName);
            }
        }
    }

    protected function addError(string $errorMessage, ?string $functionName, ?string $key) {
        $functionName ??= debug_backtrace()[1]['function'];

        if ($key !== null) {
            $this->errorsTraitErrors[$functionName][$key] = $errorMessage;
        } else {
            $this->errorsTraitErrors[$functionName][] = $errorMessage;
        }
    }

    public function getErrors(?string $functionName = null): array {
        if ($functionName !== null) {
            return $this->errorsTraitErrors[$functionName] ?? [];
        }

        return $this->errorsTraitErrors;
    }

    public function getErrorsString(string $functionName): string {
        $functionErrors = $this->errorsTraitErrors[$functionName] ?? [];

        if (count($functionErrors) === 0) {
            return "";
        }

        return Enumerable::create($functionErrors)->select(fn($errorMessage, $key) => "'{$key}': {$errorMessage}")->join("\n");
    }
}