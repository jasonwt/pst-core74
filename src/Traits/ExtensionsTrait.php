<?php

declare(strict_types=1);

namespace Pst\Core\Traits;

use Pst\Core\Func;
use Pst\Core\Action;
use Pst\Core\Types\TypeHint;

use Closure;
use InvalidArgumentException;

trait ExtensionsTrait {
    private static array $ExtentionsTrait__extensions = [];

    public static function extensionExists(string $name): bool {
        return isset(static::$ExtentionsTrait__extensions[$name]);
    }

    public static function addExtension(string $name, Closure $closure, TypeHint $returnType, TypeHint ...$parameterTypes): void {
        if (empty($name = trim($name))) {
            throw new InvalidArgumentException("Name cannot be empty.");
        } else if (!preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $name)) {
            // make sure name is a valid php function name
            throw new InvalidArgumentException("Name is not a valid PHP function name.");
        } else if (static::extensionExists($name)) {
            throw new InvalidArgumentException("Extension already exists.");
        }

        static::$ExtentionsTrait__extensions[$name] = $returnType->fullName() !== "void" ? 
            Func::new($closure, $returnType, ...$parameterTypes) : 
            Action::new($closure, ...$parameterTypes);
    }

    public static function removeExtension(string $name): void {
        if (static::extensionExists($name)) {
            unset(static::$ExtentionsTrait__extensions[$name]);
        }
    }

    public static function callExtension(string $name, mixed ...$parameters) {
        if (($extension = static::$ExtentionsTrait__extensions[$name] ?? null) === null) {
            throw new InvalidArgumentException("Extension does not exist.");
        } else if ($extension instanceof Action) {
            $extension(...$parameters);
        }

        return $extension(...$parameters);
    }
}