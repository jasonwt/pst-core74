<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\DependencyInjection;

use Pst\Core\CoreObject;
use Pst\Core\ICoreObject;
use Pst\Core\Types\Type;

use InvalidArgumentException;

class DI extends CoreObject implements ICoreObject {
    private static ?IServiceCollection $serviceCollection = null;
    private static ?IServiceProvider $serviceProvider = null;

    private function __construct() {}

    /**
     * Gets the service collection.
     * 
     * @return IServiceCollection The service collection.
     */
    private static function getServiceCollection(): IServiceCollection {
        return (self::$serviceCollection ??= new ServiceCollection());
    }

    /**
     * Gets the service provider.
     * 
     * @return IServiceProvider The service provider.
     */
    private static function getServiceProvider(): IServiceProvider {
        return (self::$serviceProvider ??= self::getServiceCollection()->createServiceProvider());
    }

    /**
     * Gets a service from the service provider.
     * 
     * @param string|Type $serviceType The type of the service.
     * 
     * @return object The service.
     */
    public static function add(ServiceDescriptor $serviceDescriptor, ?string $key = null): void {
        if (self::$serviceProvider !== null) {
            self::$serviceCollection = new ServiceCollection(self::$serviceProvider);
            self::$serviceProvider = null;
        }

        self::getServiceCollection()->add($serviceDescriptor, $key);
    }

    /**
     * Gets a service from the service provider.
     * 
     * @param string|Type $serviceType The type of the service.
     * 
     * @return object The service.
     */
    public static function get($serviceType): ?object {
        if ($serviceType instanceof Type) {
            $serviceType = $serviceType->fullName();
        } else if (!is_string($serviceType)) {
            throw new InvalidArgumentException("serviceType must be a string or Type.");
        }

        return self::getServiceProvider()->getServiceByKey($serviceType);
    }

    /**
     * Adds a transient service to the collection.
     * 
     * @param string|Type $serviceType The type of the service.
     * @param string|Type|Closure $implementation The implementation of the service.
     * @param string|null $key The key of the service.
     * 
     * @return void
     */
    public static function addTransient($serviceType, $implementation, ?string $key = null): void {
        if (is_string($serviceType)) {
            $serviceType = Type::fromTypeName(trim($serviceType));
        } else if (!$serviceType instanceof Type) {
            throw new InvalidArgumentException("Service type must be a string or a Type.");
        }

        self::add(new ServiceDescriptor(ServiceLifetime::Transient(),$serviceType,$implementation),$key);
    }

    /**
     * Adds a singleton service to the collection.
     * 
     * @param string|Type $serviceType The type of the service.
     * @param string|Type|Closure|object $implementation The implementation of the service.
     * @param string|null $key The key of the service.
     * 
     * @return void
     */

    public static function addSingleton($serviceType, $implementation, ?string $key = null): void {
        if (is_string($serviceType)) {
            $serviceType = Type::fromTypeName(trim($serviceType));
        } else if (!$serviceType instanceof Type) {
            throw new InvalidArgumentException("Service type must be a string or a Type.");
        }

        self::add(new ServiceDescriptor(ServiceLifetime::Singleton(),$serviceType,$implementation),$key);
    }
}