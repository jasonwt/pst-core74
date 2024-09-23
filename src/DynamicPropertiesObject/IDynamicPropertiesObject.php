<?php

declare(strict_types=1);

namespace Pst\Core\DynamicPropertiesObject;

use Pst\Core\Interfaces\ICoreObject;
use Pst\Core\Events\IEventSubscriptions;
use Pst\Core\Collections\IReadonlyCollection;

interface IDynamicPropertiesObject extends ICoreObject {
    public function propertyValuesSetEvent(): IEventSubscriptions;
    
    public function propertyExists(string $propertyName): bool;

    public function getPropertyValues(): IReadonlyCollection;

    public function tryGetPropertyValue(string $propertyName, &$propertyValue): bool;
    public function getPropertyValue(string $propertyName);

    public function setPropertyValues(iterable $propertyValues, bool $noThrow = true): bool;
    public function setPropertyValue(string $propertyName, $propertyValue, bool $noThrow = true): bool;
}