<?php

declare(strict_types=1);

namespace Pst\Core\Interfaces;

/**
 * Interface IConstruct
 * 
 * This interface will be used to construct objects from an array of arguments return from the IDeconstruct interface.
 * 
 * @package Pst\Core
 */
interface IConstruct {
    /**
     * Construct an object from an array of arguments.
     * 
     * @param array $args
     * 
     * @return object|null
     * 
     * @throws InvalidArgumentException
     */
    public static function construct(array $args): object;

    /**
     * Try to construct an object from an array of arguments.
     * 
     * @param array $args
     * 
     * @return object|null
     */
    public static function tryConstruct(array $args): ?object;
}