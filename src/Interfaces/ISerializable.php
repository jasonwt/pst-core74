<?php

declare(strict_types=1);

namespace Pst\Core\Interfaces;

interface ISerializable {
    public function serialize(): string;
    public static function tryUnserialize(string $serialized, &$unserializeResults): bool;
    public static function unserialize(string $serialized): self;
}