<?php

declare(strict_types=1);

namespace Pst\Collections;

use Pst\Core\CoreObject;

final class KeyValuePair extends CoreObject {
    private $key;
    private $value;

    public function __construct($key, $value) {
        $this->key = $key;
        $this->value = $value;
    }

    public function key() {
        return $this->key;
    }

    public function value() {
        return $this->value;
    }
}