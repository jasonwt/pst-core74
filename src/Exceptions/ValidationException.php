<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\Exceptions;

use InvalidArgumentException;

class ValidationException extends CoreException {
    private array $validationErrors = [];
    public function __construct(array $validationErrors, string $message = "", int $code = 0, \Throwable $previous = null) {
        if (count($validationErrors) === 0) {
            throw new InvalidArgumentException("The validation errors array must not be empty.");
        }

        if (empty($message)) {
            $message = "Validation Failures:\n" . implode("\n", array_map(fn($key, $value) => "$key: $value", array_keys($validationErrors), $validationErrors));
        }

        parent::__construct($message, $code, $previous);
    }

    public function getValidationErrors(): array {
        return $this->validationErrors;
    }
}