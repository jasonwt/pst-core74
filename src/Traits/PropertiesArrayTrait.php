<?php

declare(strict_types=1);

namespace Pst\Core\Traits;

use Pst\Core\Func;
use Pst\Core\Types\Type;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Types\ITypeHint;

use function Pst\Core\toArray;

use Closure;
use Generator;
use InvalidArgumentException;

trait PropertiesArrayTrait {
    private array $PropertiesArrayTrait = [];

    /**
     * Returns a generator that iterates over the properties
     * 
     * @return Generator 
     */
    protected function propertiesIterator(): Generator {
        foreach ($this->PropertiesArrayTrait as $name => $property) {
            yield $name => $property['value'];
        }
    }

    /**
     * Adds a property to the object
     * 
     * @param string $name 
     * @param mixed $defaultValue 
     * @param ITypeHint|null $typeHint 
     * @param Closure|null $validationClosure 
     * 
     * @return void 
     * 
     * @throws InvalidArgumentException 
     */
    protected function addProperty(string $name, $defaultValue = null, ?ITypeHint $typeHint = null, ?Closure $validationClosure = null): void {
        if (($name = trim($name)) === '') {
            throw new \InvalidArgumentException("Name cannot be empty");
        } else if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $name)) {
            // throw new \InvalidArgumentException("Name: '$name' is not a valid PHP variable name");
        }

        if (isset($this->PropertiesArrayTrait[$name])) {
            throw new InvalidArgumentException("Property with name '{$name}' already exists");
        }
        
        $typeHint ??= TypeHintFactory::mixed();

        if ($defaultValue !== null && !$typeHint->isAssignableFrom(Type::typeOf($defaultValue))) {
            throw new InvalidArgumentException("Default value is not assignable to type hint");
        }

        if ($validationClosure) {
            $validationClosure = Func::new($validationClosure, $typeHint, Type::bool());
        }

        $this->PropertiesArrayTrait[$name] = (object) [
            'value' => $defaultValue,
            'defaultValue' => $defaultValue,
            'typeHint' => (string) $typeHint,
            'validationClosure' => $validationClosure
        ];   
    }

    /**
     * Checks if a property exists
     * 
     * @param string $name
     * 
     * @return bool
     */
    protected function propertyExists(string $name): bool {
        return isset($this->PropertiesArrayTrait[$name]);
    }

    /**
     * Gets the names of the properties
     * 
     * @return array
     */
    protected function getPropertyNames(): array {
        return array_keys($this->PropertiesArrayTrait);
    }

    /**
     * Gets the specified property
     * 
     * @param string $name 
     * 
     * @return mixed 
     * 
     * @throws InvalidArgumentException 
     */
    protected function getProperty(string $name) {
        if (!isset($this->PropertiesArrayTrait[$name = trim($name)])) {
            throw new InvalidArgumentException("Property with name '{$name}' does not exist");
        }

        return $this->PropertiesArrayTrait[$name];
    }

    /**
     * Returns an array of all the registered properties
     * 
     * @return array 
     */
    protected function getProperties(): array {
        return $this->PropertiesArrayTrait;
    }

    /**
     * Gets the value of the specified property
     * 
     * @param string $name 
     * 
     * @return mixed 
     * 
     * @throws InvalidArgumentException 
     */
    protected function getPropertyValue(string $name) {
        if (!isset($this->PropertiesArrayTrait[$name = trim($name)])) {
            throw new InvalidArgumentException("Property with name '{$name}' does not exist");
        }

        return $this->PropertiesArrayTrait[$name]->value;
    }

    /**
     * Gets the values of all the properties
     * 
     * @return array 
     */
    protected function getPropertyValues(): array {
        return array_map(fn($property) => $property->value, $this->PropertiesArrayTrait);
    }

    /**
     * Sets the value of the specified property
     * 
     * @param string $name 
     * @param mixed $value 
     * 
     * @return void 
     * 
     * @throws InvalidArgumentException 
     */
    protected function setPropertyValue(string $name, $value): void {
        if (!isset($this->PropertiesArrayTrait[$name = trim($name)])) {
            throw new InvalidArgumentException("Property with name '{$name}' does not exist");
        }

        $property = $this->PropertiesArrayTrait[$name];

        if (!TypeHintFactory::tryParseTypeName($property->typeHint)->isAssignableFrom(Type::typeOf($value))) {
            throw new InvalidArgumentException("Value is not assignable to type hint");
        }

        if ($property->validationClosure && !($property->validationClosure)($value)) {
            throw new InvalidArgumentException("Invalid value for property '{$name}'");
        }

        $this->PropertiesArrayTrait[$name]->value = $value;
    }

    /**
     * Sets the values of the properties
     * 
     * @param array $values 
     * 
     * @return void 
     */
    protected function setPropertyValues(array $values): void {
        foreach ($values as $name => $value) {
            $this->setPropertyValue($name, $value);
        }
    }

    /**
     * Resets the value of the specified property to its default value
     * 
     * @param string $name 
     * 
     * @return void 
     * 
     * @throws InvalidArgumentException 
     */
    protected function resetPropertyValue(string $name): void {
        if (!isset($this->PropertiesArrayTrait[$name = trim($name)])) {
            throw new InvalidArgumentException("Property with name '{$name}' does not exist");
        }

        $this->PropertiesArrayTrait[$name]->value = $this->PropertiesArrayTrait[$name]->value;
    }

    public function toArray(): array {
        return toArray($this);
    }
}