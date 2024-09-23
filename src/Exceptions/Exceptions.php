<?php

declare(strict_types=1);

namespace Pst\Core\Exceptions;

use InvalidArgumentException;
use Pst\Core\CoreObject;

use Throwable;

class Exceptions extends CoreObject implements IExceptions {
    private array $exceptions = [];

    public function clearExceptions(?string $functionKey = null): bool {
        if ($functionKey === null) {
            $this->exceptions = [];
            return true;
        }

        if (!isset($this->exceptions[$functionKey])) {
            return false;
        }

        unset($this->exceptions[$functionKey]);

        return true;
    }

    public function removeException(string $functionKey, string $EntryKey): bool {
        if (!isset($this->exceptions[$functionKey])) {
            throw new InvalidArgumentException("Function key does not exist: $functionKey");
        }

        if (!isset($this->exceptions[$functionKey][$EntryKey])) {
            throw new InvalidArgumentException("Entry key does not exist: $EntryKey");
        }

        unset($this->exceptions[$functionKey][$EntryKey]);

        return true;
    }

    public function addException(string $functionKey, Throwable $exception, ?string $entryKey = null): bool {
        if (empty($functionKey = trim($functionKey))) {
            throw new InvalidArgumentException("Function key cannot be empty.");
        }

        if (!is_null($entryKey) && empty($entryKey = trim($entryKey))) {
            throw new InvalidArgumentException("Entry key cannot be empty.");
        }

        if ($entryKey === null) {
            $this->exceptions[$functionKey][] = $exception;
        } else {
            $this->exceptions[$functionKey][$entryKey] = $exception;
        }
        
        return true;
    }

    public function exceptionExists(string $functionKey, string $entryKey): bool {
        return isset($this->exceptions[$functionKey][$entryKey]);
    }

    public function getExceptions(?string $functionKey = null): array {
        if ($functionKey === null) {
            return $this->exceptions;
        }

        return $this->exceptions[$functionKey] ?? [];
    }

    public function getException(string $functionKey, ?string $entryKey = null): ?Throwable {
        if (!isset($this->exceptions[$functionKey])) {
            throw new InvalidArgumentException("Function key does not exist: $functionKey");
        }

        if ($entryKey === null) {
            return $this->exceptions[$functionKey][array_key_first($this->exceptions[$functionKey])];
        }

        if (!isset($this->exceptions[$functionKey][$entryKey])) {
            throw new InvalidArgumentException("Entry key does not exist: $entryKey");
        }

        return $this->exceptions[$functionKey][$entryKey];
    }
}