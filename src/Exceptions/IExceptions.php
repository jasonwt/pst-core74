<?php

declare(strict_types=1);

namespace Pst\Core\Exceptions;

use Pst\Core\Interfaces\ICoreObject;

use Throwable;

interface IExceptions extends ICoreObject {
    public function clearExceptions(?string $functionKey = null): bool;
    public function removeException(string $functionKey, string $EntryKey): bool;
    public function addException(string $functionKey, Throwable $exception, ?string $entryKey = null): bool;
    public function exceptionExists(string $functionKey, string $entryKey): bool;
    public function getExceptions(?string $functionKey = null): array;
    public function getException(string $functionKey, ?string $entryKey = null): ?Throwable;
}