<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\DependencyInjection;

use Countable;
use Pst\Core\Types\Type;
use Pst\Core\Enumerable\IEnumerable;
use Pst\Core\Enumerable\IRewindableEnumerable;

/**
 * Represents a collection of services.
 */
interface IServiceCollection extends IRewindableEnumerable, Countable {
    public function createServiceProvider(): IServiceProvider;

    /**
     * Determines whether a service with the specified key exists in the collection.
     * 
     * @param string|Type $key The key of the service.
     * 
     * @return bool True if the service exists; otherwise, false.
     */
    public function exists($typeOrKey): bool;
    public function add(ServiceDescriptor $serviceDescriptor, ?string $key = null): void;
    public function tryAdd(ServiceDescriptor $serviceDescriptor, ?string $key = null): bool;
}