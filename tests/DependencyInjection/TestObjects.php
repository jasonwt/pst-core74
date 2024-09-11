<?php

/*T-DD*/

declare(strict_types=1);

//namespace Pst\Core\Tests\DependencyInjection;

require_once(__DIR__ . "/../../vendor/autoload.php");

if (!class_exists('SoloClass')) {
    class SoloClass {}
}

if (!interface_exists('Interface1')) {
    interface Interface1 {} 
}

if (!interface_exists('Interface2')) {
    interface Interface2 {}
}

if (!class_exists('AbstractClass1')) {
    abstract class AbstractClass1 implements Interface1 {}
}

if (!class_exists('AbstractClass2')) {
    abstract class AbstractClass2 extends AbstractClass1 implements Interface2 {}
}

if (!class_exists('ConcreteClass1')) {
    class ConcreteClass1 extends AbstractClass1 implements Interface1 {}
}

if (!class_exists('ConcreteClass2')) {
    class ConcreteClass2 extends AbstractClass2 implements Interface2 {}
}