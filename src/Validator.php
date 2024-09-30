<?php

declare(strict_types=1);

namespace Pst\Core;

use Pst\Core\Types\TypeHintFactory;

use Closure;
use InvalidArgumentException;

class Validator extends CoreObject {
    private array $validators = [];

    public function __construct(array $validators = []) {
        foreach ($validators as $name => $validator) {
            $this->addValidator($name, $validator);
        }
    }

    public function addValidator(string $name, Closure $validator): void {
        if (isset($this->validators[$name])) {
            throw new InvalidArgumentException("Validator with name $name already exists");
        }

        $this->validators[$name] = Func::new($validator, TypeHintFactory::mixed(), TypeHintFactory::tryParseTypeName("bool|string"));
    }

    public function hasValidator(string $name): bool {
        return isset($this->validators[$name]);
    }

    public function validate(string $name, $value) {
        if (!isset($this->validators[$name])) {
            throw new InvalidArgumentException("Validator with name $name does not exist");
        }
        
        return $this->validators[$name]($value);
    }

    public function validateAll(array $values): array {
        return array_reduce(array_keys($values), function($acc, $name) use ($values) {
            if (($validation = $this->validate($name, $values[$name])) !== true) {
                if ($validation === false) {
                    $validation = "Validation failed with false";
                }

                $acc[$name] = $validation;
            }
            return $acc;
        }, []);
    }
}

