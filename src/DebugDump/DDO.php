<?php

declare(strict_types=1);

namespace Pst\Core\DebugDump;

use ReflectionProperty;
use ReflectionMethod;

class DDO {
    const SHOW_TRAVERSABLE_VALUES = 1;

    const SHOW_CLASS_NAMESPACE = 2;
    const SHOW_CLASS_EXTENDS = 4;
    const SHOW_CLASS_IMPLEMENTS = 8;

    const SHOW_CLASS_INSTANCE_PRIVATE_PROPERTIES = 16;
    const SHOW_CLASS_INSTANCE_PROTECTED_PROPERTIES = 32;
    const SHOW_CLASS_INSTANCE_PUBLIC_PROPERTIES = 64;
    const SHOW_CLASS_STATIC_PRIVATE_PROPERTIES = 128;
    const SHOW_CLASS_STATIC_PROTECTED_PROPERTIES = 256;
    const SHOW_CLASS_STATIC_PUBLIC_PROPERTIES = 512;

    const SHOW_CLASS_INSTANCE_PRIVATE_METHODS = 1024;
    const SHOW_CLASS_INSTANCE_PROTECTED_METHODS = 2048;
    const SHOW_CLASS_INSTANCE_PUBLIC_METHODS = 4096;
    const SHOW_CLASS_STATIC_PRIVATE_METHODS = 8192;
    const SHOW_CLASS_STATIC_PROTECTED_METHODS = 16384;
    const SHOW_CLASS_STATIC_PUBLIC_METHODS = 32768;

    const SHOW_COMMENTS = 4194304;
    const SHOW_BACKTRACE = 8388608;
    const NO_STDOUT = 16777216;

    private int $options = 0;

    /**
     * Constructor.
     * 
     * @param DDO|int ...$options 
     * @return void 
     */
    public function __construct( ...$options) {
        $this->options = array_reduce($options, function($carry, $item) {
            if ($item instanceof DDO) {
                $item = $item->options;
            } else if (!is_int($item)) {
                throw new \InvalidArgumentException("Invalid option provided");
            }

            return $carry | $item;
        }, 0);
    }

    public static function ALL_OPTIONS(): self {
        return new DDO(
            DDO::SHOW_TRAVERSABLE_VALUES |
            DDO::SHOW_CLASS_NAMESPACE |
            DDO::SHOW_CLASS_EXTENDS |
            DDO::SHOW_CLASS_IMPLEMENTS |
            
            DDO::SHOW_CLASS_INSTANCE_PRIVATE_PROPERTIES |
            DDO::SHOW_CLASS_INSTANCE_PROTECTED_PROPERTIES |
            DDO::SHOW_CLASS_INSTANCE_PUBLIC_PROPERTIES |
            DDO::SHOW_CLASS_STATIC_PRIVATE_PROPERTIES |
            DDO::SHOW_CLASS_STATIC_PROTECTED_PROPERTIES |
            DDO::SHOW_CLASS_STATIC_PUBLIC_PROPERTIES |
            DDO::SHOW_CLASS_INSTANCE_PRIVATE_METHODS |
            DDO::SHOW_CLASS_INSTANCE_PROTECTED_METHODS |
            DDO::SHOW_CLASS_INSTANCE_PUBLIC_METHODS |
            DDO::SHOW_CLASS_STATIC_PRIVATE_METHODS |
            DDO::SHOW_CLASS_STATIC_PROTECTED_METHODS |
            DDO::SHOW_CLASS_STATIC_PUBLIC_METHODS |
            DDO::SHOW_COMMENTS |
            DDO::SHOW_BACKTRACE
        );
    }

    public static function DEFAULT_OPTIONS(): DDO {
        return new DDO(
            DDO::SHOW_CLASS_NAMESPACE |
            DDO::SHOW_CLASS_EXTENDS |
            DDO::SHOW_CLASS_IMPLEMENTS | 
            DDO::SHOW_CLASS_INSTANCE_PRIVATE_PROPERTIES |
            DDO::SHOW_CLASS_INSTANCE_PROTECTED_PROPERTIES |
            DDO::SHOW_CLASS_INSTANCE_PUBLIC_PROPERTIES |
            DDO::SHOW_COMMENTS |
            DDO::SHOW_BACKTRACE
        );
    }

    public static function CLASS_METHODS(): DDO {
        return new DDO(
            DDO::SHOW_CLASS_INSTANCE_PRIVATE_METHODS |
            DDO::SHOW_CLASS_INSTANCE_PROTECTED_METHODS |
            DDO::SHOW_CLASS_INSTANCE_PUBLIC_METHODS |
            DDO::SHOW_CLASS_STATIC_PRIVATE_METHODS |
            DDO::SHOW_CLASS_STATIC_PROTECTED_METHODS |
            DDO::SHOW_CLASS_STATIC_PUBLIC_METHODS
        );
    }

    public static function PRIVATE_CLASS_METHODS(): DDO {
        return new DDO(
            DDO::SHOW_CLASS_INSTANCE_PRIVATE_METHODS |
            DDO::SHOW_CLASS_STATIC_PRIVATE_METHODS
        );
    }

    public static function PROTECTED_CLASS_METHODS(): DDO {
        return new DDO(
            DDO::SHOW_CLASS_INSTANCE_PROTECTED_METHODS |
            DDO::SHOW_CLASS_STATIC_PROTECTED_METHODS
        );
    }

    public static function PUBLIC_CLASS_METHODS(): DDO {
        return new DDO(
            DDO::SHOW_CLASS_INSTANCE_PUBLIC_METHODS |
            DDO::SHOW_CLASS_STATIC_PUBLIC_METHODS
        );
    }

    public static function STATIC_CLASS_METHODS(): DDO {
        return new DDO(
            DDO::SHOW_CLASS_STATIC_PRIVATE_METHODS |
            DDO::SHOW_CLASS_STATIC_PROTECTED_METHODS |
            DDO::SHOW_CLASS_STATIC_PUBLIC_METHODS
        );
    }

    public static function INSTANCE_CLASS_METHODS(): DDO {
        return new DDO(
            DDO::SHOW_CLASS_INSTANCE_PRIVATE_METHODS |
            DDO::SHOW_CLASS_INSTANCE_PROTECTED_METHODS |
            DDO::SHOW_CLASS_INSTANCE_PUBLIC_METHODS
        );
    }

    public static function CLASS_PROPERTIES(): DDO {
        return new DDO(
            DDO::SHOW_CLASS_INSTANCE_PRIVATE_PROPERTIES |
            DDO::SHOW_CLASS_INSTANCE_PROTECTED_PROPERTIES |
            DDO::SHOW_CLASS_INSTANCE_PUBLIC_PROPERTIES |
            DDO::SHOW_CLASS_STATIC_PRIVATE_PROPERTIES |
            DDO::SHOW_CLASS_STATIC_PROTECTED_PROPERTIES |
            DDO::SHOW_CLASS_STATIC_PUBLIC_PROPERTIES
        );
    }

    public static function PRIVATE_CLASS_PROPERTIES(): DDO {
        return new DDO(
            DDO::SHOW_CLASS_INSTANCE_PRIVATE_PROPERTIES |
            DDO::SHOW_CLASS_STATIC_PRIVATE_PROPERTIES
        );
    }

    public static function PROTECTED_CLASS_PROPERTIES(): DDO {
        return new DDO(
            DDO::SHOW_CLASS_INSTANCE_PROTECTED_PROPERTIES |
            DDO::SHOW_CLASS_STATIC_PROTECTED_PROPERTIES
        );
    }

    public static function PUBLIC_CLASS_PROPERTIES(): DDO {
        return new DDO(
            DDO::SHOW_CLASS_INSTANCE_PUBLIC_PROPERTIES |
            DDO::SHOW_CLASS_STATIC_PUBLIC_PROPERTIES
        );
    }

    public static function STATIC_CLASS_PROPERTIES(): DDO {
        return new DDO(
            DDO::SHOW_CLASS_STATIC_PRIVATE_PROPERTIES |
            DDO::SHOW_CLASS_STATIC_PROTECTED_PROPERTIES |
            DDO::SHOW_CLASS_STATIC_PUBLIC_PROPERTIES
        );
    }

    public static function INSTANCE_CLASS_PROPERTIES(): DDO {
        return new DDO(
            DDO::SHOW_CLASS_INSTANCE_PRIVATE_PROPERTIES |
            DDO::SHOW_CLASS_INSTANCE_PROTECTED_PROPERTIES |
            DDO::SHOW_CLASS_INSTANCE_PUBLIC_PROPERTIES
        );
    }

    public static function TRAVERSABLE_VALUES(): DDO {
        return new DDO(DDO::SHOW_TRAVERSABLE_VALUES);
    }

    /**
     * Disables the specified options.
     * 
     * @param DDO|int ...$options 
     * @return void 
     */
    public function disableOptions(...$options) {
        $this->options &= ~array_reduce($options, function($carry, $item) {
            if ($item instanceof DDO) {
                $item = $item->options;
            } else if (!is_int($item)) {
                throw new \InvalidArgumentException("Invalid option provided");
            }

            return $carry | $item;
        }, 0);
    }

    /**
     * Enables the specified options.
     * 
     * @param DDO|int ...$options 
     * @return void 
     */
    public function enableOptions(...$options) {
        $this->options |= array_reduce($options, function($carry, $item) {
            if ($item instanceof DDO) {
                $item = $item->options;
            } else if (!is_int($item)) {
                throw new \InvalidArgumentException("Invalid option provided");
            }

            return $carry | $item;
        }, 0);
    }

    public function isOptionSet(int $option): bool {
        return ($this->options & $option) === $option;
    }

    public function shouldShowClassProperties(int $reflectionPropertyProtection, $reflectionPropertyIsStatic) {
        if ($reflectionPropertyIsStatic) {
            if ($reflectionPropertyProtection === ReflectionProperty::IS_PRIVATE) {
                return $this->isOptionSet(DDO::SHOW_CLASS_STATIC_PRIVATE_PROPERTIES);
            } else if ($reflectionPropertyProtection === ReflectionProperty::IS_PROTECTED) {
                return $this->isOptionSet(DDO::SHOW_CLASS_STATIC_PROTECTED_PROPERTIES);
            } else if ($reflectionPropertyProtection === ReflectionProperty::IS_PUBLIC) {
                return $this->isOptionSet(DDO::SHOW_CLASS_STATIC_PUBLIC_PROPERTIES);
            }
        } else {
            if ($reflectionPropertyProtection === ReflectionProperty::IS_PRIVATE) {
                return $this->isOptionSet(DDO::SHOW_CLASS_INSTANCE_PRIVATE_PROPERTIES);
            } else if ($reflectionPropertyProtection === ReflectionProperty::IS_PROTECTED) {
                return $this->isOptionSet(DDO::SHOW_CLASS_INSTANCE_PROTECTED_PROPERTIES);
            } else if ($reflectionPropertyProtection === ReflectionProperty::IS_PUBLIC) {
                return $this->isOptionSet(DDO::SHOW_CLASS_INSTANCE_PUBLIC_PROPERTIES);
            }
        }
    }

    public function shouldShowClassMethods(int $reflectionMethodProtection, $reflectionMethodIsStatic) {
        if ($reflectionMethodIsStatic) {
            if ($reflectionMethodProtection === ReflectionMethod::IS_PRIVATE) {
                return $this->isOptionSet(DDO::SHOW_CLASS_STATIC_PRIVATE_METHODS);
            } else if ($reflectionMethodProtection === ReflectionMethod::IS_PROTECTED) {
                return $this->isOptionSet(DDO::SHOW_CLASS_STATIC_PROTECTED_METHODS);
            } else if ($reflectionMethodProtection === ReflectionMethod::IS_PUBLIC) {
                return $this->isOptionSet(DDO::SHOW_CLASS_STATIC_PUBLIC_METHODS);
            }
        } else {
            if ($reflectionMethodProtection === ReflectionMethod::IS_PRIVATE) {
                return $this->isOptionSet(DDO::SHOW_CLASS_INSTANCE_PRIVATE_METHODS);
            } else if ($reflectionMethodProtection === ReflectionMethod::IS_PROTECTED) {
                return $this->isOptionSet(DDO::SHOW_CLASS_INSTANCE_PROTECTED_METHODS);
            } else if ($reflectionMethodProtection === ReflectionMethod::IS_PUBLIC) {
                return $this->isOptionSet(DDO::SHOW_CLASS_INSTANCE_PUBLIC_METHODS);
            }
        }
    }

    public function __toString(): string {
        return (string) $this->options;
    }

    public function options(): int {
        return $this->options;
    }
}