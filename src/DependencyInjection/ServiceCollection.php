<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\DependencyInjection;

use Closure;
use Pst\Core\Func;
use Pst\Core\CoreObject;
use Pst\Core\Types\Type;
use Pst\Core\Collections\CollectionTrait;
use Pst\Core\Exceptions\NotImplementedException;

use Pst\Core\Enumerable\IEnumerable;
use Pst\Core\Enumerable\Enumerable;
use Pst\Core\Enumerable\ImmutableEnumerableLinqTrait;
use Pst\Core\Collections\Collection;
use Pst\Core\Collections\ICollection;
use Pst\Core\Collections\IReadonlyCollection;
use Pst\Core\Collections\ReadOnlyCollection;
use Pst\Core\Enumerable\Linq\EnumerableLinqTrait;

use Pst\Core\Exceptions\ContainerException;
use Pst\Core\Exceptions\DependencyNotFoundException;




use IteratorAggregate;

class ServiceCollection extends CoreObject implements IteratorAggregate, IServiceCollection {
    use CollectionTrait {
        //contains as private;
        //containsKey as private;
        tryAdd as private;
        add as private;
        clear as private;
        remove as private;

        __construct as private collectionTraitConstruct;
    }

    public function __construct(iterable $services = []) {
        $this->collectionTraitConstruct($services, Type::class(ServiceDescriptor::class), Type::string());

        foreach ($services as $alias => $service) {
            $this->add($service, $alias);
        }
    }

    /**
     * Determines whether a service with the specified key exists in the collection.
     * 
     * @param string|Type $key The key of the service.
     * 
     * @return bool True if the service exists; otherwise, false.
     */
    public function exists($typeOrKey): bool {
        if ($typeOrKey instanceof Type) {
            $typeOrKey = $typeOrKey->fullName();
        } else if (!is_string($typeOrKey)) {
            throw new ContainerException("Type or key must be a string or Type.");
        }

        return $this->containsKey($typeOrKey);
    }

    /**
     * Tries to add a service to the collection.
     * 
     * @param ServiceDescriptor $serviceDescriptor The service descriptor to add.
     * @param string|null $key The key of the service.
     * 
     * @return bool True if the service was added; otherwise, false.
     * 
     * @throws ContainerException
     */
    public function tryAdd(ServiceDescriptor $serviceDescriptor, ?string $serviceKey = null): bool {
        $serviceKey ??= $serviceDescriptor->getServiceType()->fullName();

        if (is_numeric($serviceKey)) {
            throw new ContainerException("Service key must be a non numeric string.");
        }

        $serviceType = $serviceDescriptor->getServiceType();

        $serviceKey ??= $serviceType->fullName();

        if (isset($this->keyValues[$serviceKey])) {
            return false;
        }

        if (!$serviceType->isInterface() && !$serviceType->isClass()) {
            throw new ContainerException("Service type must be an interface or a class.");
        }

        $this->offsetSet($serviceKey, $serviceDescriptor);

        return true;
    }

    /**
     * Adds a service to the collection.
     * 
     * @param ServiceDescriptor $serviceDescriptor The service descriptor to add.
     * @param string|null $key The key of the service.
     * 
     * @return void
     * 
     * @throws ContainerException
     */
    public function add(ServiceDescriptor $serviceDescriptor, ?string $serviceKey = null): void {
        $serviceKey ??= $serviceDescriptor->getServiceType()->fullName();
        
        if (!$this->tryAdd($serviceDescriptor, $serviceKey)) {
            throw new ContainerException("A service with the serviceKey '$serviceKey' already exists.");
        }
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
        return ServiceProvider::create($this);
    }
    
}