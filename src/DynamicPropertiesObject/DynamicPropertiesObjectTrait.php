<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\DynamicPropertiesObject;

use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Events\Event;
use Pst\Core\Events\IEvent;
use Pst\Core\Events\IEventSubscriptions;
use Pst\Core\Exceptions\Exceptions;
use Pst\Core\Exceptions\IExceptions;
use Pst\Core\Enumerable\Enumerator;
use Pst\Core\Collections\ReadonlyCollection;
use Pst\Core\Collections\IReadonlyCollection;

use InvalidArgumentException;

/**
 * Trait DynamicPropertiesObjectTrait
 * 
 * This trait provides the implementation for the IDynamicPropertiesObject interface.
 * 
 */
trait DynamicPropertiesObjectTrait {
    private array $dynamicPropertiesObjectTrait = [
        "propertyValues" => null,
        "exceptions" => null,
        "propertyValuesSetEvent" => null,
    ];

    /**
     * Validates the property name.
     */
    public static function validatePropertyName(string $propertyName): bool {
        return preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $propertyName) !== false;
    }

    /**
     * DynamicPropertiesObjectTrait constructor.
     * 
     * @param iterable $propertyValues The property values.
     * 
     * @throws InvalidArgumentException
     */
    public function __construct(iterable $propertyValues) {
        $this->dynamicPropertiesObjectTrait["propertyValues"] = ReadonlyCollection::create($propertyValues);

        $propertyValidations = [];
        $setPropertyNames = [];

        foreach ($this->propertyValues() as $propertyName => $propertyValue) {
            if (isset($propertyValidations[$propertyName = trim($propertyName)])) {
                $propertyValidations["propertyNameAlreadySet"][$propertyName] = "Property name already set: $propertyName";
            }

            $setPropertyNames[$propertyName] = $propertyName;

            if (!static::validatePropertyName($propertyName)) {
                $propertyValidations[$propertyName] = "$propertyName: Invalid property name.";
            } else if (!$this->validatePropertyValue($propertyName, $propertyValue)) {
                $propertyValidations[$propertyName] = $this->exceptions()->getException("validatePropertyValue")->getMessage();
            }
        }

        $exceptionMessage = "";

        if (isset($propertyValidations["propertyNameAlreadySet"])) {
            $exceptionMessage .= implode("\n", $propertyValidations["propertyNameAlreadySet"]) . "\n";
            unset($propertyValidations["propertyNameAlreadySet"]);
        }

        $exceptionMessage .= implode("\n", $propertyValidations);

        if (!empty($exceptionMessage)) {
            throw new InvalidArgumentException("\n\n" . $exceptionMessage . "\n\n");
        }
    }

    protected function propertyValues(): IReadonlyCollection {
        return $this->dynamicPropertiesObjectTrait["propertyValues"];
    }

    /**
     * Gets the exceptions object.
     * 
     * @return IExceptions The exceptions object.
     */
    protected function exceptions(): IExceptions {
        return $this->dynamicPropertiesObjectTrait["exceptions"] ??= new Exceptions();
    }

    /**
     * Gets the property values set event object.
     * 
     * @return IEvent The property values set event object.
     */
    protected function propertyValuesSetEventObject(): IEvent {
        return $this->dynamicPropertiesObjectTrait["propertyValuesSetEvent"] ??= 
            new Event($this, TypeHintFactory::string(), TypeHintFactory::mixed(), TypeHintFactory::mixed());
    }

    /**
     * Invokes the property values set event.
     * 
     * @param mixed ...$args The arguments.
     */
    protected function onPropertyValuesSetEvent(... $args) {
        $this->propertyValuesSetEventObject()->invoke(... $args);
    }

    /**
     * Validates the property value.
     * 
     * @param string $propertyName The property name.
     * @param mixed $internalPropertyValue The internal property value.
     * 
     * @return bool True if the input value is valid; otherwise, false.
     */
    protected function validatePropertyValue(string $propertyName, $internalPropertyValue): bool {
        $this->exceptions()->clearExceptions(__FUNCTION__);
        return true;
    }

    /**
     * Converts the external property value to an internal property value.
     * 
     * @param string $propertyName The property name.
     * @param mixed $externalPropertyValue The external property value.
     * 
     * @return mixed The internal property value.
     */
    protected function toInternalPropertyValue(string $propertyName, $externalPropertyValue) {
        return $externalPropertyValue;
    }

    /**
     * Converts the internal property values to external property values.
     * 
     * @param iterable $internalPropertyValues The internal property values.
     * 
     * @return IReadonlyCollection The external property values.
     */
    protected function fromInternalPropertyValues(iterable $internalPropertyValues): IReadonlyCollection {
        return ReadonlyCollection::create($internalPropertyValues)->
            select(fn($internalPropertyValue, $propertyName) => $this->fromInternalPropertyValue($propertyName, $internalPropertyValue))->
            toReadonlyCollection();
    }

    /**
     * Converts the internal property value to an external property value.
     * 
     * @param string $propertyName The property name.
     * @param mixed $internalPropertyValue The internal property value.
     * 
     * @return mixed The external property value.
     */
    protected function fromInternalPropertyValue(string $propertyName, $internalPropertyValue) {;
        return $internalPropertyValue;
    }

    /**
     * Gets the property values set event.
     * 
     */
    public function propertyValuesSetEvent(): IEventSubscriptions {
        return $this->propertyValuesSetEventObject();
    }

    /**
     * Determines if a property exists.
     * 
     * @param string $propertyName The property name.
     * 
     * @return bool True if the property exists; otherwise, false.
     */
    public function propertyExists(string $propertyName): bool {
        return $this->propertyValues()->offsetExists($propertyName);
    }

    /**
     * Gets the property values.
     * 
     * @return IReadonlyCollection The property values.
     */
    public function getPropertyValues(): IReadonlyCollection {
        return Enumerator::create($this->propertyValues())->
            select(fn($propertyValue, $propertyName) => $this->fromInternalPropertyValue($propertyName, $propertyValue))->
            toReadonlyCollection();
    }

    /**
     * Tries to get the property value.
     * 
     * @param string $propertyName The property name.
     * @param mixed $propertyValue The property value.
     * 
     * @return bool True if the property value was found; otherwise, false.
     */
    public function tryGetPropertyValue(string $propertyName, &$propertyValue): bool {
        $this->exceptions()->clearExceptions(__FUNCTION__);

        if (empty($propertyName = trim($propertyName))) {
            $this->exceptions()->addException(__FUNCTION__, new InvalidArgumentException("Property name cannot be empty."));
            return false;
        } else if (!$this->propertyValues()->offsetExists($propertyName)) {
            $this->exceptions()->addException(__FUNCTION__, new InvalidArgumentException("Property '$propertyName' does not exist."), $propertyName);
            return false;
        }

        $propertyValue = $this->fromInternalPropertyValue($propertyName, $this->propertyValues()[$propertyName]);
        return true;
    }

    /**
     * Gets the property value.
     * 
     * @param string $propertyName The property name.
     * 
     * @return mixed The property value.
     */
    public function getPropertyValue(string $propertyName) {
        if (!$this->tryGetPropertyValue($propertyName, $propertyValue)) {
            throw $this->exceptions()->getException("tryGetPropertyValue");
        }

        return $propertyValue;
    }

    /**
     * Sets the property values.
     * 
     * @param iterable $propertyValues The property values.
     * @param bool $noThrow True to suppress exceptions; otherwise, false.
     * 
     * @return bool True if the property values were set; otherwise, false.
     */
    public function setPropertyValues(iterable $propertyValues, bool $noThrow = true): bool {
        $this->exceptions()->clearExceptions(__FUNCTION__);

        $currentPropertyValues = $this->propertyValues();

        $changedPropertyValues = Enumerator::create($propertyValues)->
            select(function($externalPropertyValue, $propertyName) {
                if (empty($propertyName = trim($propertyName))) {
                    $this->exceptions()->addException("setPropertyValues", new InvalidArgumentException("Property name cannot be empty."), "nullPropertyName");
                    return null;
                } else if (!$this->propertyExists($propertyName)) {
                    $this->exceptions()->addException("setPropertyValues", new InvalidArgumentException("Property '$propertyName' does not exist."), $propertyName);
                    return null;
                }

                $internalPropertyValue = $this->toInternalPropertyValue($propertyName, $externalPropertyValue);

                if (!$this->validatePropertyValue($propertyName, $internalPropertyValue)) {
                    $this->exceptions()->addException("setPropertyValues", $this->exceptions()->getException("validatePropertyValue"), $propertyName);
                    return null;
                }
        
                return $internalPropertyValue;
            })->
            where((fn($propertyValue, $propertyName) => $propertyValue != $currentPropertyValues[$propertyName]))->
            toReadonlyCollection();

        if (!empty($exceptions = $this->exceptions()->getExceptions(__FUNCTION__))) {
            if (!$noThrow) {
                throw $this->exceptions()->getException(__FUNCTION__);
            }
            
            return false;
        }

        $this->dynamicPropertiesObjectTrait["propertyValues"] = ReadonlyCollection::create(
            $changedPropertyValues->toArray() + $currentPropertyValues->toArray()
        );

        foreach ($changedPropertyValues as $propertyName => $newPropertyValue) {
            $this->onPropertyValuesSetEvent($propertyName, $currentPropertyValues[$propertyName], $newPropertyValue);
        }

        return true;
    }

    /**
     * Sets the property value.
     * 
     * @param string $propertyName The property name.
     * @param mixed $propertyValue The property value.
     * @param bool $noThrow True to suppress exceptions; otherwise, false.
     * 
     * @return bool True if the property value was set; otherwise, false.
     */
    public function setPropertyValue(string $propertyName, $propertyValue, bool $noThrow = true): bool {
        return $this->setPropertyValues([$propertyName => $propertyValue], $noThrow);
    }
}