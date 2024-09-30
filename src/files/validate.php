<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;



function validate_string($value, int $minLength, int $maxLength) {
    if (!is_string($value)) {
        throw new \InvalidArgumentException("Value must be a string.");
    } else if (strlen($value) < $minLength) {
        return "cannot be shorter than $minLength characters.";
    } else if (strlen($value) > $maxLength) {
        return "cannot be longer than $maxLength characters.";
    }

    return true;
}

function validate_int($value, int $minValue, int $maxValue) {
    if (!is_int($value)) {
        throw new \InvalidArgumentException("Value must be an integer.");
    } else if ($value < $minValue || $value > $maxValue) {
        return "must be between $minValue and $maxValue.";
    }

    return true;
}