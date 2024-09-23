<?php

declare(strict_types=1);

namespace Pst\Core;

trait SerializableTrait {
    public function serialize(): string {
        return serialize($this);
    }

    public static function tryUnserialize(string $serialized, &$unserializeResults): bool {
        $unserializeResults = unserialize($serialized);
        return $unserializeResults !== false;
    }

    public static function unserialize(string $serialized): self {
        if (!self::tryUnserialize($serialized, $unserializeResults)) {
            throw new \Exception('Failed to unserialize');
        }

        return $unserializeResults;
    }
}