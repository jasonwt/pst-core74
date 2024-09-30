<?php

/*TDD*/

declare(strict_types=1);

namespace EnumTests;

use Pst\Core\DynamicPropertiesObject\DynamicPropertiesObject;

use Pst\Testing\Should;

use Traversable;

require_once(__DIR__ . "/../vendor/autoload.php");

Should::executeTests(function() {
    
    // class TestDynamicPropertiesObject extends DynamicPropertiesObject {
    //     public function __construct(iterable $propertyValues = []) {
            
    //         $propertyValues = ($propertyValues instanceof Traversable) ? iterator_to_array($propertyValues) : $propertyValues;

    //         parent::__construct(
    //             $propertyValues +
    //             [
    //                 "test" => "empty"
    //             ]
    //         );
    //     }
    // }
    
    // $dynamicPropertyObject = new TestDynamicPropertiesObject();
    // $dynamicPropertyObject->propertyValuesSetEvent()->attach(function($sender, string $propertyName, $oldPropertyValue, $newPropertyValue) {
    //     echo "Property '$propertyName' changed from '$oldPropertyValue' to '$newPropertyValue'.\n";
    // });
    
    // $dynamicPropertyObject->getPropertyValue("test");
    // $dynamicPropertyObject->setPropertyValue("test", "Hello, World!");
});