<?php

declare(strict_types=1);

namespace Pst\Core\Interfaces;

/**
 * Interface IDeconstruct
 * 
 * This interface will return constructor arguments to recreate the object with the interface IConstruct.
 * 
 * @package Pst\Core
 */
interface IDeconstruct {
    /**
     * Deconstruct an object into an array of arguments.
     * 
     * @return array
     * 
     */
    public function deconstruct(): array;
}