<?php

declare(strict_types=1);

namespace Pst\Core\Traits;

use Pst\Core\Types\Type;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Types\ITypeHint;

use Pst\Core\Runtime\Settings;
use Pst\Core\Runtime\Traits\RuntimeSettingsTrait;

use Closure;
use ReflectionFunction;
use InvalidArgumentException;
use Pst\Core\Types\TypeHintFactoryFactory;

trait TypedClosureTrait {
    use RuntimeSettingsTrait;

    private Closure $TypedClosureTrait__closure;
    private string $TypedClosureTrait__validReturnTypes;
    private array $TypedClosureTrait__validParametersTypes = [];

    /**
     * Initializes the runtime settings.
     * 
     * @return void
     */
    protected static function initRuntimeSettings(): void {
        Settings::tryRegisterSetting(static::class . "::returnValueValidation", true, TypeHintFactory::bool());
        Settings::tryRegisterSetting(static::class . "::parameterValidation", true, TypeHintFactory::bool());
    }

    /**
     * Returns if return type validation is enabled.
     * 
     * @return bool The return type validation setting.
     */
    public static function getReturnTypeValidation(): bool {
        return static::getRuntimeSetting("returnValueValidation");
    }

    /**
     * Disables return type validation
     * 
     * @return void
     */
    public static function disableReturnTypeValidation(): void {
        static::setRuntimeSetting("returnValueValidation", false);
    }

    /**
     * Enables return type validation
     * 
     * @return void
     */
    public static function enableReturnTypeValidation(): void {
        static::setRuntimeSetting("returnValueValidation", true);
    }

    /**
     * Returns if parameter validation is enabled.
     * 
     * @return bool The parameter validation setting.
     */
    public static function getParameterValidation(): bool {
        return static::getRuntimeSetting("parameterValidation");
    }

    /**
     * Disables parameter validation
     * 
     * @return void
     */
    public static function disableParameterValidation(): void {
        static::setRuntimeSetting("parameterValidation", false);
    }

    /**
     * Enables parameter validation
     * 
     * @return void
     */
    public static function enableParameterValidation(): void {
        static::setRuntimeSetting("parameterValidation", true);
    }

    /**
     * Returns if any validation is enabled.
     * 
     * @return bool The validation setting.
     */
    public static function getValidation(): bool {
        return static::getReturnTypeValidation() || static::getParameterValidation();
    }

    /**
     * Disables all validation
     * 
     * @return void
     */
    public static function disableValidation(): void {
        static::disableReturnTypeValidation();
        static::disableParameterValidation();
    }

    /**
     * Enables all validation
     * 
     * @return void
     */
    public static function enableValidation(): void {
        static::enableReturnTypeValidation();
        static::enableParameterValidation();
    }

    /**
     * Initializes a new instance of the TypedClosureTrait class.
     * 
     * @param Closure $closure The closure.
     * @param ITypeHint $validClosureReturnTypes The valid return type hint of the closure.
     * @param ITypeHint ...$validClosureParametersTypes The valid parameter type hints of the closure.
     * 
     * @throws InvalidArgumentException
     */
    public function __construct(Closure $closure, ITypeHint $validClosureReturnTypes, ITypeHint ...$validClosureParametersTypes) {
        //print_r(func_get_args());
        $this->TypedClosureTrait__closure = $closure;

        $validateReturnType = static::getReturnTypeValidation();
        $validateParameters = static::getParameterValidation();

        list ($closureReturnTypeHintName, $closureParametersTypeHintNames) = array_values(self::closureTypeInfo($closure));

        $this->TypedClosureTrait__validReturnTypes = (string) $validClosureReturnTypes;
        $this->TypedClosureTrait__validParametersTypes = array_map(fn($v) => $v->fullName(), $validClosureParametersTypes);

        if ($validateReturnType) {
            if ($this->TypedClosureTrait__validReturnTypes !== "undefined" && $this->TypedClosureTrait__validReturnTypes !== $closureReturnTypeHintName) {
                if ($this->TypedClosureTrait__validReturnTypes === "void" && $closureReturnTypeHintName !== "undefined") {
                    throw new InvalidArgumentException("The valid return type hint must be 'void'.");
                }

                if ($closureReturnTypeHintName !== "undefined") {
                    $closureReturnTypeHint = TypeHintFactory::tryParse($closureReturnTypeHintName);

                    if ($closureReturnTypeHintName === "void" || $this->TypedClosureTrait__validReturnTypes === "void" || !$validClosureReturnTypes->isAssignableFrom($closureReturnTypeHint)) {
                        throw new InvalidArgumentException("The closure return type hint: '{$closureReturnTypeHintName}' is not assignable to type hint: '{$this->TypedClosureTrait__validReturnTypes}'.");
                    }
                }
            }
        }

        if (count($closureParametersTypeHintNames) !== count($validClosureParametersTypes)) {
            throw new InvalidArgumentException("The closure has '" . count($closureParametersTypeHintNames) . "' parameters, but '" . count($validClosureParametersTypes) . "' type hints were specified.");
        }

        if ($validateParameters) {
            $closureParameterNames = array_keys($closureParametersTypeHintNames);

            for ($i = 0; $i < count($validClosureParametersTypes); $i ++) {
                $closureParameterName = $closureParameterNames[$i];

                if (($closureParameterTypeHintName = $closureParametersTypeHintNames[$closureParameterNames[$i]]) === "void") {
                    throw new InvalidArgumentException("The closure parameter type hint for property: '$closureParameterName' can not be void.");
                }

                if (($validClosureParameterTypeHintName = $this->TypedClosureTrait__validParametersTypes[$i]) === "void") {
                    throw new InvalidArgumentException("The valid closure parameter type hint for property: '$closureParameterName' can not be void.");
                }

                if ($validClosureParameterTypeHintName !== "undefined" && $validClosureParameterTypeHintName !== $closureParameterTypeHintName) {
                    if ($closureParameterTypeHintName !== "undefined") {
                        $validClosureParameterTypeHint = TypeHintFactory::tryParse($validClosureParameterTypeHintName);
                        $closureParameterTypeHint = TypeHintFactory::tryParse($closureParameterTypeHintName);

                        if (!$validClosureParameterTypeHint->isAssignableFrom($closureParameterTypeHint)) {
                            throw new InvalidArgumentException("The valid closure parameter type: '{$closureParameterTypeHintName}' for property: '$closureParameterName' is not assignable to type hint: '{$validClosureParameterTypeHintName}'.");
                        }
                    }
                }
            }
        } 
    }

    /**
     * Invokes the closure.
     * 
     * @param mixed ...$args The arguments to pass to the closure.
     * 
     * @return mixed The result of the closure.
     * 
     * @throws InvalidArgumentException
     */
    public function __invoke(...$args) {
        if (count($args) !== count($this->TypedClosureTrait__validParametersTypes)) {
            throw new InvalidArgumentException("The number of invoke arguments: '" . count($args) . "' does not match the number of parameters: '" . count($this->TypedClosureTrait__validParametersTypes) . "'.");
        }

        if (static::getParameterValidation()) {
            $validParameterNames = array_keys($this->TypedClosureTrait__validParametersTypes);

            foreach ($validParameterNames as $parameterIndex => $validParameterName) {
                $validParameterTypes = TypeHintFactory::tryParse($this->TypedClosureTrait__validParametersTypes[$validParameterName]);
                $parameter = $args[$parameterIndex];

                $valueType = Type::typeOf($parameter);

                if (!$validParameterTypes->isAssignableFrom($valueType)) {
                    throw new InvalidArgumentException("The invoke parameter type: '" . $valueType->fullName() . "' for parameter: '$validParameterName' is not assignable to the type hint: '{$this->TypedClosureTrait__validParametersTypes[$validParameterName]}'.");
                }
            }
        }

        $result = ($this->TypedClosureTrait__closure)(...$args);

        if ($this->TypedClosureTrait__validReturnTypes !== "void") {
            if (!static::getReturnTypeValidation()) {
                return $result;
            }

            $returnType = Type::typeOf($result);

            $closureReturnType = TypeHintFactory::tryParse($this->TypedClosureTrait__validReturnTypes);

            if (!$closureReturnType->isAssignableFrom($returnType)) {
                throw new InvalidArgumentException("The invoke return type: '{$returnType}' is not assignable to the valid return type hint: '{$this->TypedClosureTrait__validReturnTypes}'.");
            }
            
            return $result;
        }
    }

    /**
     * Gets the closure.
     * 
     * @return Closure The closure.
     */
    protected function getClosure(): Closure {
        return $this->TypedClosureTrait__closure;
    }

    /**
     * Gets the parameter names of the closure.
     * 
     * @return array The parameter names of the closure.
     */
    protected function getParameterNames(): array {
        return array_keys($this->TypedClosureTrait__validParametersTypes);
    }

    /**
     * Gets the return type hint of the closure.
     * 
     * @return ITypeHint The return type hint of the closure.
     */
    protected function getReturnTypeHint(): ITypeHint {
        return TypeHintFactory::tryParse($this->TypedClosureTrait__validReturnTypes);
    }

    /**
     * Gets the parameter type hints of the closure.
     * 
     * @return array An array containing the return type hint and parameter type hints.
     */
    protected function getParameterTypeHints(): array {
        return array_map(fn($v) => TypeHintFactory::tryParse($v), $this->TypedClosureTrait__validParametersTypes);
    }

    /**
     * Gets the type hint of the parameter at the specified key or index.
     * 
     * @param string|int $keyOrIndex The key or index of the parameter.
     * 
     * @return TypeHint The type hint of the parameter.
     * 
     * @throws InvalidArgumentException
     */
    protected function getParameterTypeHint($keyOrIndex): ITypeHint {
        if (is_string($keyOrIndex)) {
            if (!isset($this->TypedClosureTrait__validParametersTypes[$keyOrIndex])) {
                throw new InvalidArgumentException("Parameter '" . $keyOrIndex . "' does not exist.");
            }

            return TypeHintFactory::tryParse($this->TypedClosureTrait__validParametersTypes[$keyOrIndex]);
        } else if (!is_int($keyOrIndex)) {
            throw new InvalidArgumentException("Parameter key must be an integer or string.");
        }

        $parameterKeys = array_keys($this->TypedClosureTrait__validParametersTypes);

        if (!isset($parameterKeys[$keyOrIndex])) {
            throw new InvalidArgumentException("Parameter at index " . $keyOrIndex . " does not exist.");
        }

        return TypeHintFactory::tryParse($this->TypedClosureTrait__validParametersTypes[$parameterKeys[$keyOrIndex]]);
    }

    /**
     * Gets the return type hint and parameter type hints of the closure.
     * 
     * @param Closure $closure The closure.
     * 
     * @return array An array containing the return type hint and parameter type hints.
     */
    protected static function closureTypeInfo(Closure $closure): array {
        $results = [
            "returnTypeHint" => null,
            "parameterTypeHints" => []
        ];

        $reflection = new ReflectionFunction($closure);

        if (!$reflection->hasReturnType()) {
            $results["returnTypeHint"] = "undefined";
        } else {
            $returnType = $reflection->getReturnType();

            if (method_exists($returnType, "getTypes")) {
                $results["returnTypeHint"] = implode("|", array_map(fn($v) => call_user_func([$v, "getName"]), call_user_func([$returnType, "getTypes"])));
            } else {
                $results["returnTypeHint"] = $returnType->getName() . ($returnType->allowsNull() ? "|null" : "");
            }
        }

        $results["parameterTypeHints"] = array_reduce($reflection->getParameters(), function($parameters, $parameter) {
            $parameterTypeNames = [];

            if (!$parameter->hasType()) {
                $parameterTypeNames = "undefined";
            } else {
                $parameterType = $parameter->getType();
                if (method_exists($parameterType, "getTypes")) {
                    $parameterTypeNames = implode("|", array_map(fn($v) => call_user_func([$v, "getName"]), call_user_func([$parameterType, "getTypes"])));
                } else {
                    $parameterTypeNames = $parameterType->getName() . ($parameterType->allowsNull() ? "|null" : "");
                }
            }

            $parameters[$parameter->getName()] = $parameterTypeNames;
            
            return $parameters;
        }, []);

        return $results;
    }
}