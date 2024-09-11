<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\DependencyInjection;

use Pst\Core\Types\Type;
use Pst\Core\Collections\IEnumerable;

use Countable;

/**
 * Represents a service provider.
 */
interface IServiceProvider extends IEnumerable, Countable {
    public function toServiceCollection(): IServiceCollection;
    public function getService(Type $serviceType): ?object;
    public function getServiceByKey(string $serviceType): ?object;
}