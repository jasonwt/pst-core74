<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;

use Exception;
use InvalidArgumentException;

/**
 * An abstract class that defines an enum.
 * 
 * @package PST\Core
 * 
 * @version 1.0.0
 * 
 * @since 1.0.0
 * 
 */
abstract class Enum extends CoreObject {
    private $value;

    protected function __construct($value) {
        $this->value = $value;
    }

    protected static function caseAliases(): array {
        return [];
    }

    public function value() {
        return $this->value;
    }

    public function name() {
        return array_search($this->value, static::cases());
    }

    public function __toString(): string {
        return (string) $this->value;
    }

    /**
     * Creates an enum from a value.

    * @param $name 
    * @param $arguments 

    * @return static 
    
    * @throws Exception 
    */
    public static function __callStatic($name, $arguments) {
        if (count($arguments) != 0) {
            throw new Exception("Invalid enum value");
        }

        if (empty($name = trim(strtoupper($name)))) {
            throw new Exception("Invalid enum value");
        }

        if (!array_key_exists($name, static::cases())) {
            throw new Exception("Invalid enum value");
        }

        return new static($name);
    }

    /**
     * Gets the cases of the enum.
     * 
     * @return array
     */
    public abstract static function cases(): array;

    /**
     * Tries to create an enum from a name.
     * 
     * @param string $name 
     * 
     * @return null|Enum 
     */
    public static function tryFromName(string $name): ?Enum {
        $name = static::caseAliases()[$name] ?? $name;

        $cases = static::cases();

        if (!array_key_exists($name, $cases)) {
            return null;
        }

        return new static($cases[$name]);
    }

    /**
     * Creates an enum from a name.
     * 
     * @param string $name 
     * 
     * @return Enum 
     * 
     * @throws InvalidArgumentException 
     */
    public static function fromName(string $name): Enum {
        $name = static::caseAliases()[$name] ?? $name;

        $cases = static::cases();

        if (!array_key_exists($name, $cases)) {
            print_r(static::cases());
            throw new InvalidArgumentException("Invalid enum name: '{$name}'");
        }

        return new static($cases[$name]);
    }

    /**
     * Creates an enum from a value.
     * 
     * @param $value
     * 
     * @return static
     * 
     * @throws Exception
     */
    public static function from($value) {
        $cases = static::cases();

        if (!in_array($value, $cases, true)) {
            throw new Exception("Invalid enum value");
        }

        return new static($value);
    }

    /**
     * Tries to create an enum from a value.
     * 
     * @param $value
     * 
     * @return static|null
     */
    public static function tryFrom($value): ?Enum {
        if (empty($value = trim($value))) {
            return null;
        }

        $cases = static::cases();

        if (!in_array($value, $cases, true)) {
            return null;
        }

        return new static($value);
    }

    /**
     * Gets the preg match pattern for the enum.
     * 
     * @param string $delimiter 
     * 
     * @return string
     */
    public static function getPregMatchPattern(string $delimiter = "/"): string {
        $cases = static::cases();

        // order cases from longest string value to shortest
        uasort($cases, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        return "(" . implode('|', array_map(function($case) use ($delimiter) {
            //return preg_quote($case, $delimiter);
            $padding = "(?:" . (!ctype_alpha($case) ? '\s*' : '\s+') . ")";

            return $padding . preg_quote($case, $delimiter) . $padding;
        }, $cases)) . ")";
    }
}