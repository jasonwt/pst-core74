<?php

/*TDD*/

declare(strict_types=1);

require_once(__DIR__ . "/../../vendor/autoload.php");

use Pst\Core\DependencyInjection\IServiceCollection;
use Pst\Core\DependencyInjection\ServiceCollection;
use Pst\Core\DependencyInjection\ServiceDescriptor;
use Pst\Core\DependencyInjection\ServiceLifetime;

use Pst\Core\Exceptions\ContainerException;

use Pst\Testing\Should;

require_once(__DIR__ . "/TestObjects.php");

Should::executeTests(function() {
    $serviceCollection = new ServiceCollection();
    Should::beTrue($serviceCollection instanceof IServiceCollection);

    Should::notThrow(Exception::class, fn() => $serviceCollection->add(new ServiceDescriptor(ServiceLifetime::Transient(), Interface1::class, ConcreteClass1::class)));
    Should::notThrow(ContainerException::class, fn() => $serviceCollection->add(new ServiceDescriptor(ServiceLifetime::Transient(), Interface1::class, ConcreteClass1::class), "SOMEALIAS"));

    Should::notThrow(Exception::class, fn() => $serviceCollection->add(new ServiceDescriptor(ServiceLifetime::Singleton(), Interface2::class, ConcreteClass2::class)));
    Should::notThrow(ContainerException::class, fn() => $serviceCollection->add(new ServiceDescriptor(ServiceLifetime::Transient(), Interface2::class, ConcreteClass2::class), "SOMEUNUSEDALIAS"));

    Should::notThrow(Exception::class, fn() => $serviceCollection->add(new ServiceDescriptor(ServiceLifetime::Singleton(), Interface1::class, new ConcreteClass1()), "SINGLETONLIAS"));

    Should::notBeNull(($serviceProvider = $serviceCollection->createServiceProvider()));

    Should::beA(($transientService = $serviceProvider->getServiceByKey(Interface1::class)), ConcreteClass1::class, Interface1::class);
    Should::beA(($transientService2 = $serviceProvider->getServiceByKey("SOMEALIAS")), ConcreteClass1::class, Interface1::class);

    Should::beA(($singletonService = $serviceProvider->getServiceByKey(Interface2::class)), ConcreteClass2::class, Interface2::class);
    Should::beA(($singletonService2 = $serviceProvider->getServiceByKey("SOMEUNUSEDALIAS")), ConcreteClass2::class, Interface2::class);

    Should::beA(($singletonService3 = $serviceProvider->getServiceByKey("SINGLETONLIAS")), ConcreteClass1::class, Interface1::class);
});