<?php

/*TDD*/

declare(strict_types=1);

//namespace Pst\Core\Tests\DependencyInjection;

require_once(__DIR__ . "/../../vendor/autoload.php");

use Pst\Core\DependencyInjection\IServiceCollection;
use Pst\Core\DependencyInjection\IServiceProvider;
use Pst\Core\DependencyInjection\ServiceCollection;
use Pst\Core\DependencyInjection\ServiceDescriptor;
use Pst\Core\DependencyInjection\ServiceLifetime;

use Pst\Testing\Should;

use Pst\Core\Exceptions\ContainerException;

require_once(__DIR__ . "/TestObjects.php");

Should::executeTests(function() {
    $serviceCollection = new ServiceCollection();
    Should::beTrue($serviceCollection instanceof IServiceCollection);

    // Should not throw because SoloClass::class is assignable to SoloClass::class
    Should::notThrow(Exception::class, fn() => $serviceCollection->add(new ServiceDescriptor(ServiceLifetime::Transient(), SoloClass::class, SoloClass::class)));
    // Should throw because SoloClass does not implement Interface1
    Should::throw(ContainerException::class, fn() => $serviceCollection->add(new ServiceDescriptor(ServiceLifetime::Transient(), Interface1::class, SoloClass::class)));

    // Should not throw because ConcreteClass1::class is assignable to Interface1::class
    Should::notThrow(Exception::class, fn() => $serviceCollection->add(new ServiceDescriptor(ServiceLifetime::Transient(), Interface1::class, ConcreteClass1::class)));
    // Should throw because the service is already registered
    Should::throw(ContainerException::class, fn() => $serviceCollection->add(new ServiceDescriptor(ServiceLifetime::Transient(), Interface1::class, ConcreteClass1::class)));
    // Should not throw because we are using a non used alias
    Should::notThrow(ContainerException::class, fn() => $serviceCollection->add(new ServiceDescriptor(ServiceLifetime::Transient(), Interface1::class, ConcreteClass1::class), "SOMEALIAS"));

    Should::notThrow(Exception::class, fn() => $serviceCollection->add(new ServiceDescriptor(ServiceLifetime::Singleton(), Interface2::class, ConcreteClass2::class)));
    // Should throw because the service is already registered
    Should::throw(ContainerException::class, fn() => $serviceCollection->add(new ServiceDescriptor(ServiceLifetime::Singleton(), Interface2::class, ConcreteClass2::class)));
    // Should throw because the alias specified is already in use
    Should::throw(ContainerException::class, fn() => $serviceCollection->add(new ServiceDescriptor(ServiceLifetime::Transient(), Interface2::class, ConcreteClass2::class), "SOMEALIAS"));
    // Should not throw because we are using a non used alias
    Should::notThrow(ContainerException::class, fn() => $serviceCollection->add(new ServiceDescriptor(ServiceLifetime::Transient(), Interface2::class, ConcreteClass2::class), "SOMEUNUSEDALIAS"));

    // Should throw because you can not add a instance of an object with a non singleton lifetime
    Should::throw(ContainerException::class, fn() => $serviceCollection->add(new ServiceDescriptor(ServiceLifetime::Transient(), Interface1::class, new ConcreteClass1())));
    Should::notThrow(Exception::class, fn() => $serviceCollection->add(new ServiceDescriptor(ServiceLifetime::Singleton(), Interface1::class, new ConcreteClass1()), "SINGLETONLIAS"));

    Should::notBeNull(($serviceProvider = $serviceCollection->createServiceProvider()));
    Should::beA($serviceProvider, IServiceProvider::class);
});