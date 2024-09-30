<?php

declare(strict_types=1);

namespace Pst\Core\Types;

use Pst\Core\CoreObject;
use Pst\Core\Types\Type;
use Pst\Core\Types\TypeHintFactory;
use Pst\Core\Types\ITypeHint;
use Pst\Core\Traits\ShouldTypeCheckTrait;

use Closure;
use ReflectionFunction;
use InvalidArgumentException;

trait TypedClosureTrait {
    use ShouldTypeCheckTrait;

    private static bool $strictTypes = false;

    public static function enableStrictTypes(): void {
        static::$strictTypes = true;
    }

    public static function disableStrictTypes(): void {
        static::$strictTypes = false;
    }

    public static function isStrictTypesEnabled(): bool {
        return static::$strictTypes;
    }

    private Closure $typedClosure;

    private ITypeHint $validReturnType;
    private array $validParameterTypes = [];

    private int $minRequiredParameters = 0;

    private bool $typeCheckReturnType = false;
    private bool $typeCheckParameters = false;

    /**
     * Initializes a new instance of the TypedClosureTrait class.
     * 
     * @param Closure $closure The closure.
     * @param ITypeHint $validClosureReturnTypes The valid return type hint of the closure.
     * @param null|ITypeHint ...$validClosureParametersTypes The valid parameter type hints of the closure.
     * 
     * @throws InvalidArgumentException
     */
    private function __construct(Closure $closure, ITypeHint $validClosureReturnTypes, ?ITypeHint ...$validClosureParametersTypes) {
        $this->typedClosure = $closure;

        list ($closureReturnType, $closureParameterTypes) = array_values(self::internalClosureTypeInfo($closure));

        if ($validClosureReturnTypes == "void") {
            if ($closureReturnType != "void" && $closureReturnType != "undefined") {
                throw new InvalidArgumentException("The valid return type hint must be 'void'.");
            }
        } else if ($validClosureReturnTypes == "undefined") {
            if (($validClosureReturnTypes = $closureReturnType) == "undefined" && static::$strictTypes) {
                throw new InvalidArgumentException("A return type must be specified by the closure or the validClosureReturnTypes parameter.");
            }
        } else if ($closureReturnType == "undefined") {
            $this->typeCheckReturnType = true;

        } else if (!$closureReturnType->isAssignableFrom($validClosureReturnTypes)) {
            throw new InvalidArgumentException("The closure return type hint: '{$closureReturnType}' is not assignable to type hint: '{$validClosureReturnTypes}'.");
        }

        if (count($validClosureParametersTypes) < count($closureParameterTypes)) {
            throw new InvalidArgumentException("The closure has '" . count($closureParameterTypes) . "' parameters, but only '" . count($validClosureParametersTypes) . "' parameter types were specified.");
        }

        $this->validReturnType = $validClosureReturnTypes;

        $i = 0;

        foreach ($closureParameterTypes as $closureParameterName => $closureParameterType) {
            $validParameterType = $validClosureParametersTypes[$i];

            if ($validParameterType === "void") {
                throw new InvalidArgumentException("The specified valid parameter type for the provided closure parameter: '{$closureParameterName}' can not be void.");
            }

            if ($validParameterType instanceof TypedClosureOptionalParameter) {
                if ($i < count($validClosureParametersTypes) - 1 && !$validClosureParametersTypes[$i + 1] instanceof TypedClosureOptionalParameter) {
                    throw new InvalidArgumentException("Optionals parameters must the last parameter or followed by another optional parameter.");
                }

                $this->validParameterTypes[$closureParameterName] = $validParameterType;

                $this->minRequiredParameters --;

                continue;
            }

            if ($validParameterType == "undefined") {
                if (($validParameterType = $closureParameterType) == "undefined" && static::$strictTypes) {
                    throw new InvalidArgumentException("The closure parameter type for property: '$closureParameterName' should not be undefined.");
                }
            } else if ($closureParameterType == "undefined") {
                $this->typeCheckParameters = true;

                
            } else if (!$closureParameterType->isAssignableFrom($validParameterType)) {
                throw new InvalidArgumentException("The specified valid parameter type: '{$validParameterType}' for closure parameter: '{$closureParameterName}' is not assignable to the provided closure parameter type: '{$closureParameterType}'.");    
            }

            $this->validParameterTypes[$closureParameterName] = $validParameterType;

            $i ++;
        }

        while ($i < count($validClosureParametersTypes)) {
            $validParameterType = $validClosureParametersTypes[$i];
            $closureParameterName = "optional" . ($i - $this->minRequiredParameters + 1);

            if ($validParameterType === "void") {
                throw new InvalidArgumentException("The specified valid parameter type for the provided closure parameter: 'optional" . ($i - $this->minRequiredParameters + 1) . "' can not be void.");
            }

            if (!$validParameterType instanceof TypedClosureOptionalParameter) {
                throw new InvalidArgumentException("The closure has '" . count($closureParameterTypes) . "' parameters, but '" . count($validClosureParametersTypes) . "' type hints were specified.");
            } else if ($i < count($validClosureParametersTypes) - 1 && !$validClosureParametersTypes[$i + 1] instanceof TypedClosureOptionalParameter) {
                throw new InvalidArgumentException("Optionals parameters must be the last parameter or followed by another optional parameter.");
            }

            $this->validParameterTypes[$closureParameterName] = $validParameterType;

            $i ++;
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
        $argsCount = count($args);

        if ($argsCount < $this->minRequiredParameters || $argsCount > count($this->validParameterTypes)) {
            throw new InvalidArgumentException("The number of invoke arguments: '" . $argsCount . "' is less than the minimum required parameters: '" . $this->minRequiredParameters . "'.");
        }

        $validateReturnType = $this->willValidateReturnType();
        $validateParameters = $this->willValidateParameters();

        if (!$validateParameters && !$validateReturnType) {
            return ($this->typedClosure)(...$args);
        }

        if ($validateParameters) {
            $i = 0;

            foreach ($this->validParameterTypes as $closureParameterName => $validParameterType) {
                if ($i >= $argsCount) {
                    break;
                }

                $invokeParameter = $args[$i];

                $invokedParameterType = Type::typeOf($invokeParameter);

                if (!$validParameterType->isAssignableFrom($invokedParameterType)) {
                    throw new InvalidArgumentException("The invoke parameter type: '{$invokedParameterType}' for parameter: '$closureParameterName' is not assignable to the type hint: '{$validParameterType}'.");
                }

                $i ++;
            }
        }

        $result = ($this->typedClosure)(...$args);

        if ($validateReturnType) {
            $invokedReturnType = Type::typeOf($result);

            if (!$this->validReturnType->isAssignableFrom($invokedReturnType)) {
                throw new InvalidArgumentException("The invoke return type: '{$invokedReturnType}' is not assignable to the valid return type hint: '{$this->validReturnType}'.");
            }
        }

        return $result;
    }

    protected function getMinRequiredParameters(): int {
        return $this->minRequiredParameters;
    }

    protected function willValidateReturnType(): bool {
        return self::shouldTypeCheck() && $this->typeCheckReturnType;
    }

    protected function willValidateParameters(): bool {
        return self::shouldTypeCheck() && $this->typeCheckParameters;
    }

    /**
     * Gets the closure.
     * 
     * @return Closure The closure.
     */
    protected function getClosure(): Closure {
        return $this->typedClosure;
    }

    /**
     * Gets the parameter names of the closure.
     * 
     * @return array The parameter names of the closure.
     */
    protected function getParameterNames(): array {
        return array_keys($this->validParameterTypes);
    }

    /**
     * Gets the return type hint of the closure.
     * 
     * @return ITypeHint The return type hint of the closure.
     */
    protected function getReturnTypeHint(): ITypeHint {
        return $this->validReturnType;
    }

    /**
     * Gets the parameter type hints of the closure.
     * 
     * @return array An array containing the return type hint and parameter type hints.
     */
    protected function getParameterTypeHints(): array {
        return $this->validParameterTypes;
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
            if (!isset($this->validParameterTypes[$keyOrIndex])) {
                throw new InvalidArgumentException("Parameter '" . $keyOrIndex . "' does not exist.");
            }

            return TypeHintFactory::tryParseTypeName($this->validParameterTypes[$keyOrIndex]);
        } else if (!is_int($keyOrIndex)) {
            throw new InvalidArgumentException("Parameter key must be an integer or string.");
        }

        $parameterKeys = array_keys($this->validParameterTypes);

        if (!isset($parameterKeys[$keyOrIndex])) {
            throw new InvalidArgumentException("Parameter at index " . $keyOrIndex . " does not exist.");
        }

        return TypeHintFactory::tryParseTypeName($this->validParameterTypes[$parameterKeys[$keyOrIndex]]);
    }

    protected static function internalClosureTypeInfo(Closure $closure): array {
        $results = [
            "returnTypeHint" => null,
            "parameterTypeHints" => []
        ];

        $reflection = new ReflectionFunction($closure);

        if (!$reflection->hasReturnType()) {
            $results["returnTypeHint"] = TypeHintFactory::undefined();
        } else {
            $returnType = $reflection->getReturnType();

            if (method_exists($returnType, "getTypes")) {
                $results["returnTypeHint"] = TypeHintFactory::new(
                    implode("|", array_map(fn($v) => call_user_func([$v, "getName"]), call_user_func([$returnType, "getTypes"])))
                );
            } else {
                $results["returnTypeHint"] = TypeHintFactory::new(
                    $returnType->getName() . ($returnType->allowsNull() ? "|null" : "")
                );
            }
        }

        $results["parameterTypeHints"] = array_reduce($reflection->getParameters(), function($parameters, $parameter) {
            $parameterTypes = [];

            if (!$parameter->hasType()) {
                $parameterTypes = TypeHintFactory::undefined();
            } else {
                $parameterType = $parameter->getType();
                if (method_exists($parameterType, "getTypes")) {
                    $parameterTypes = TypeHintFactory::new(
                        implode("|", array_map(fn($v) => call_user_func([$v, "getName"]), call_user_func([$parameterType, "getTypes"])))
                    );
                } else {
                    $parameterTypes = TypeHintFactory::new(
                        $parameterType->getName() . ($parameterType->allowsNull() ? "|null" : "")
                    );
                }
            }

            $parameters[$parameter->getName()] = $parameterTypes;
            
            return $parameters;
        }, []);

        return $results;
    }

    public static function optionalParameter(ITypeHint $typeHint): TypedClosureOptionalParameter {
        return new TypedClosureOptionalParameter($typeHint);
    }
}