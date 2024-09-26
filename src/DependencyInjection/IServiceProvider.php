<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\DependencyInjection;

use Pst\Core\Types\Type;
use Pst\Core\Enumerable\IRewindableEnumerable;

use Countable;

/**
 * Represents a service provider.
 */
interface IServiceProvider extends IRewindableEnumerable, Countable {
    public function getService(Type $serviceType): ?object;
    public function getServiceByKey(string $serviceType): ?object;
    public function toServiceCollection(): IServiceCollection;
}