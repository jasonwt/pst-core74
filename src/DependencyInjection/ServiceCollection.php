<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\DependencyInjection;

use Pst\Core\Func;
use Pst\Core\CoreObject;
use Pst\Core\Types\Type;
use Pst\Core\Enumerable\IEnumerable;
use Pst\Core\Enumerable\Enumerator;
use Pst\Core\Enumerable\ImmutableEnumerableLinqTrait;
use Pst\Core\Collections\Collection;
use Pst\Core\Collections\ICollection;
use Pst\Core\Collections\IReadonlyCollection;
use Pst\Core\Collections\ReadOnlyCollection;
use Pst\Core\Enumerable\Linq\EnumerableLinqTrait;

use Pst\Core\Exceptions\ContainerException;
use Pst\Core\Exceptions\DependencyNotFoundException;

use Closure;
use Traversable;
use ArrayIterator;
use Iterator;
use IteratorAggregate;
use ReflectionClass;

class ServiceCollection extends CoreObject implements Iterator, IServiceCollection {
    use EnumerableLinqTrait {

    }

    private array $services = [];
    private ICollection $serviceCollection;

    /**
     * Initializes a new instance of ServiceCollection.
     * 
     * @param IEnumerable|array $services The services to add to the collection.
     */
    public function __construct(iterable $services = []) {
        $this->serviceCollection = Collection::create($services, Type::typeOf(ServiceDescriptor::class));

        foreach (Enumerator::create($services) as $alias => $service) {
            if (!is_string($alias) || is_numeric($alias)) {
                throw new ContainerException("Service alias must be a string.");
            }

            $this->serviceCollection->add($service, $alias);
        }
    }

    public function getIterator(): Traversable {
        return $this->serviceCollection;
    }

    public function count(?Closure $predicate = null): int {
        return $predicate === null ? count($this->services) : $this->linqCount($predicate);
    }

    
    public function T(): Type {
        return Type::typeOf(ServiceDescriptor::class);
    }

    /**
     * Determines whether a service with the specified key exists in the collection.
     * 
     * @param string|Type $typeOrKey The key of the service.
     * 
     * @return bool True if the service exists; otherwise, false.
     */
    public function exists($typeOrKey): bool {
        if ($typeOrKey instanceof Type) {
            $typeOrKey = $typeOrKey->fullName();
        } else if (!is_string($typeOrKey)) {
            throw new ContainerException("Type or key must be a string or Type.");
        }

        return isset($this->services[$typeOrKey]);
    }

    /**
     * Adds a service to the collection.
     * 
     * @param ServiceDescriptor $serviceDescriptor
     * @param null|string $serviceKey
     * 
     * @return void
     * 
     * @throws ContainerException
     */
    public function add(ServiceDescriptor $serviceDescriptor, ?string $serviceKey = null): void {
        $serviceKey ??= $serviceDescriptor->getServiceType()->fullName();

        if (is_numeric($serviceKey)) {
            throw new ContainerException("Service key must be a non numeric string.");
        }

        $serviceType = $serviceDescriptor->getServiceType();

        $serviceKey ??= $serviceType->fullName();

        if (isset($this->services[$serviceKey])) {
            throw new ContainerException("A service with the serviceKey '$serviceKey' already exists.");
        }

        if (!$serviceType->isInterface() && !$serviceType->isClass()) {
            throw new ContainerException("Service type must be an interface or a class.");
        }

        $this->services[$serviceKey] = $serviceDescriptor;
    }

    /**
     * Creates a service provider from the collection.
     * 
     * @return IServiceProvider
     * 
     * @throws DependencyNotFoundException
     * @throws ContainerException
     */
    public function createServiceProvider(): IServiceProvider {
        return new class($this->services) extends CoreObject implements IteratorAggregate, IServiceProvider {
            use ImmutableEnumerableLinqTrait {
                linqKeys as keys;
                linqValues as values;
            }

            private array $services = [];

            public function __construct(array $services) {
                $this->services = $services;
            }

            public function getImmutableIterator(): Traversable {
                return $this->getIterator();
            }

            public function getIterator(): Traversable {
                return new ArrayIterator($this->services);
            }

            public function count(?Closure $predicate = null): int {
                return $predicate === null ? count($this->services) : $this->linqCount($predicate);
            }

            public function T(): Type {
                return Type::typeOf(ServiceDescriptor::class);
            }

            public function toServiceCollection(): IServiceCollection {
                return new ServiceCollection($this->services);
            }

            public function getService(Type $serviceType): ?object {
                return $this->getServiceByKey($serviceType->fullName());
            }

            public function getServiceByKey(string $serviceKey): ?object {
                if (($service = ($this->services[$serviceKey] ?? null)) === null) {
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
                    $this->services[$serviceKey] = new ServiceDescriptor($serviceLifetime, $serviceType, $classInstance);
                }

                return $classInstance;
            }
        };
    }

    public function toArray(): array {
        return $this->serviceCollection->toArray();
    }

    public function toCollection(): ICollection {
        return Collection::create($this->serviceCollection);
    }

    public function toReadonlyCollection(): IReadonlyCollection {
        return ReadonlyCollection::create($this->serviceCollection);
    }

    
}