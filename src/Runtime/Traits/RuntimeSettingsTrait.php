<?php

declare(strict_types=1);

namespace Pst\Core\Runtime\Traits;

use Pst\Core\Runtime\Settings;

trait RuntimeSettingsTrait {
    private static bool $RuntimeSetting__initiated = false;
    protected abstract static function initRuntimeSettings(): void;

    public static function getRuntimeSetting(string $name) {
        if (empty($name = trim($name))) {
            throw new \InvalidArgumentException("Name cannot be empty");
        }

        if (!static::$RuntimeSetting__initiated) {
            static::initRuntimeSettings();
            static::$RuntimeSetting__initiated = true;
        }

        return Settings::getSetting(static::class . "::" . $name);
    }

    public static function setRuntimeSetting(string $name, $value): void {
        if (empty($name = trim($name))) {
            throw new \InvalidArgumentException("Name cannot be empty");
        }

        if (!static::$RuntimeSetting__initiated) {
            static::initRuntimeSettings();
            static::$RuntimeSetting__initiated = true;
        }

        Settings::setSetting(static::class . "::" . $name, $value);
    }
}