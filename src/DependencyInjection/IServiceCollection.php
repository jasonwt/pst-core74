<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\DependencyInjection;


use Pst\Core\Types\Type;
use Pst\Core\Collections\IEnumerable;

use Countable;


/**
 * Represents a collection of services.
 */
interface IServiceCollection extends IEnumerable, Countable {
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
}