<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core\DependencyInjection;

use Pst\Core\Func;
use Pst\Core\CoreObject;
use Pst\Core\Interfaces\ICoreObject;
use Pst\Core\Types\Type;

use Pst\Core\Exceptions\ContainerException;

use Closure;
use InvalidArgumentException;

/**
 * Represents a service descriptor.
 */
class ServiceDescriptor extends CoreObject implements ICoreObject {
    private ServiceLifetime $lifetime;
    private Type $serviceType;
    private $implementation;

    /**
     * Initializes a new instance of ServiceDescriptor with a string implementation.
     * 
     * @param ServiceLifetime $lifetime The lifetime of the service.
     * @param Type $serviceType The type of the service.
     * @param string $implementation The implementation of the service.
     * 
     * @throws ContainerException
     */
    private static function initStringImplementation(ServiceLifetime $lifetime, Type $serviceType, string $implementation): Type {
        if (empty($implementation = trim($implementation))) {
            throw new InvalidArgumentException("Implementation cannot be empty.");
        }

        if (($implementationTypeInfo = Type::getTypeInfo($implementation)) === null) {
            throw new ContainerException("Implementation is not a valid type.");
        } else if ($implementationTypeInfo["isClass"] === false) {
            throw new ContainerException("Implementation must be a non abstract class.");
        } else if ($implementationTypeInfo["isAbstract"] === true) {
            throw new ContainerException("Implementation must be a non abstract class.");
        }

        $implementation = Type::new($implementation);

        if ($serviceType->isAssignableFrom($implementation) === false) {
            throw new ContainerException("Implementation must be a subclass of the service type.");
        }

        return $implementation;
    }

    /**
     * Initializes a new instance of ServiceDescriptor with a Type implementation.
     * 
     * @param ServiceLifetime $lifetime The lifetime of the service.
     * @param Type $serviceType The type of the service.
     * @param Type $implementation The implementation of the service.
     * 
     * @throws ContainerException
     */
    private static function initTypeImplementation(ServiceLifetime $lifetime, Type $serviceType, Type $implementation): Type {
        if ($serviceType->isAssignableFrom($implementation) === false) {
            throw new ContainerException("Implementation must be a subclass of the service type.");
        }

        return $implementation;
    }

    /**
     * Initializes a new instance of ServiceDescriptor with a Closure implementation.
     * 
     * @param ServiceLifetime $lifetime The lifetime of the service.
     * @param Type $serviceType The type of the service.
     * @param Closure $implementation The implementation of the service.
     * 
     * @throws ContainerException
     */
    private static function initClosureImplementation(ServiceLifetime $lifetime, Type $serviceType, Closure $implementation): Func {
        return Func::new($implementation, $serviceType);
    }

    /**
     * Initializes a new instance of ServiceDescriptor with an object implementation.
     * 
     * @param ServiceLifetime $lifetime The lifetime of the service.
     * @param Type $serviceType The type of the service.
     * @param object $implementation The implementation of the service.
     * 
     * @throws ContainerException
     */
    private static function initObjectImplementation(ServiceLifetime $lifetime, Type $serviceType, object $implementation): object {
        if (!$serviceType->isAssignableFrom(Type::typeOf($implementation))) {
            throw new ContainerException("Implementation must be a subclass of the service type.");
        } else if ($lifetime != ServiceLifetime::Singleton()) {
            throw new ContainerException("Concrete implementations must be have a lifetime of ServiceLifetime::Singleton().");
        }

        return $implementation;
    }


    /**
     * Creates a new instance of ServiceDescriptor.
     * 
     * @param ServiceLifetime $lifetime The lifetime of the service.
     * @param string|Type $serviceType The type of the service.
     * @param string|Type|Closure|object $implementation The implementation of the service.
     * 
     * @throws ContainerException
     */
    public function __construct(ServiceLifetime $lifetime, $serviceType, $implementation) {
        if (is_string($serviceType)) {
            $serviceType = Type::new($serviceType);
        } else if (!$serviceType instanceof Type) {
            throw new ContainerException("Service type must be a string or a Type.");
        }

        if (!$serviceType->isInterface() && !$serviceType->isClass()) {
            throw new ContainerException("Service type must be an interface or a class.");
        }

        if (is_string($implementation)) {
            $implementation = self::initStringImplementation($lifetime, $serviceType, $implementation);

        } else if ($implementation instanceof Type) {
            $implementation = self::initTypeImplementation($lifetime, $serviceType, $implementation);

        } else if ($implementation instanceof Closure) {
            $implementation = self::initClosureImplementation($lifetime, $serviceType, $implementation);

        } else if (is_object($implementation)) {
            $implementation = self::initObjectImplementation($lifetime, $serviceType, $implementation);
            
        } else {
            throw new ContainerException("Implementation must be a string, a class, a closure, or an object.");
        }

        $this->lifetime = $lifetime;
        $this->serviceType = $serviceType;
        $this->implementation = $implementation;
    }

    /**
     * Gets the lifetime of the service.
     * 
     * @return ServiceLifetime The lifetime of the service.
     */
    public function getLifetime(): ServiceLifetime {
        return $this->lifetime;
    }

    /**
     * Gets the type of the service.
     * 
     * @return Type The type of the service.
     */
    public function getServiceType(): Type {
        return $this->serviceType;
    }

    /**
     * Gets the implementation of the service.
     * 
     * @return object The implementation of the service.
     */
    public function getImplementation(): object {
        return $this->implementation;
    }

    public function __toString(): string {
        return $this->lifetime . " " . $this->serviceType . " " . $this->implementation;
    }
}