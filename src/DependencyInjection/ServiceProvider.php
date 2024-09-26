<?php

declare(strict_types=1);

namespace Pst\Core\DependencyInjection;

use IteratorAggregate;
use Pst\Core\Func;
use Pst\Core\CoreObject;
use Pst\Core\Types\Type;
use Pst\Core\Collections\ReadonlyCollectionTrait;

use Pst\Core\Exceptions\DependencyNotFoundException;

use ReflectionClass;

class ServiceProvider extends CoreObject implements IteratorAggregate, IServiceProvider {
    use ReadonlyCollectionTrait {
        __construct as private readonlyCollectionConstruct;
    }

    protected function __construct(IServiceCollection $serviceCollection) {
        $this->readonlyCollectionConstruct($serviceCollection);
    }

    public function getService(Type $serviceType): ?object {
        return $this->getServiceByKey($serviceType->fullName());
    }

    public function getServiceByKey(string $serviceKey): ?object {
        if (($service = ($this->keyValues[$serviceKey] ?? null)) === null) {
            return null;
        }

        $serviceLifetime = $service->getLifetime();
        $serviceType = $service->getServiceType();
        $serviceImplementation = $service->getImplementation();

        if ($serviceImplementation instanceof Func) {
            $classInstance = $serviceImplementation();
        } else if (!$serviceImplementation instanceof Type) {
            return $serviceImplementation;
        } else {
            $classReflection = new ReflectionClass($serviceImplementation->fullName());
            $classInstance = null;

            if (($classConstructor = $classReflection->getConstructor()) === null) {
                $classInstance = $classReflection->newInstance();
            } else if (empty($classConstructorParameters = $classConstructor->getParameters())) {
                $classInstance = $classReflection->newInstance();
            } else {
                $constructorArguments = array_reduce(range(0, count($classConstructorParameters) - 1), function($acc, $i) use ($classConstructorParameters): ?array {
                    if ($acc === null) {
                        return null;
                    }

                    $parameter = $classConstructorParameters[$i];
                    $parameterType = $parameter->getType();
                    $parameterTypeName = $parameterType->getName();

                    if (($resolvedParameterValue = $this->getService(Type::typeOf($parameterTypeName))) !== null) {
                        $acc[] = $resolvedParameterValue;
                    } else if ($parameter->isDefaultValueAvailable()) {
                        $acc[] = $parameter->getDefaultValue();
                    } else {
                        return null;
                    }

                    return $acc;
                }, []);

                if ($constructorArguments === null) {
                    throw new DependencyNotFoundException("Cannot resolve constructor arguments for class '$serviceImplementation'.");
                }

                $classInstance = $classReflection->newInstanceArgs($constructorArguments);
            }
        }

        if ($serviceLifetime === ServiceLifetime::Singleton()) {
            $this->keyValues[$serviceKey] = new ServiceDescriptor($serviceLifetime, $serviceType, $classInstance);
        }

        return $classInstance;
    }

    /**
     * Converts the service provider to a service collection.
     * 
     * @return IServiceCollection The service collection.
     */
    public function toServiceCollection(): IServiceCollection {
        return new ServiceCollection($this->keyValues);
    }

    /**
     * Creates a new instance of ServiceProvider.
     * 
     * @param IServiceCollection $serviceCollection The service collection.
     * 
     * @return IServiceProvider The service provider.
     */
    public static function create(IServiceCollection $serviceCollection) {
        return new ServiceProvider($serviceCollection);
    }
}