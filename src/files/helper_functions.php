<?php
/*__FILEDOCBLOCK__*/

declare(strict_types=1);

namespace Pst\Core;

// if (!function_exists("enum_exists")) {
//     function enum_exists(string $name): bool {
//         return is_a($name, Enum::class, true);
//     }
// }

// function is_enum($value): bool {
//     if (!is_object($value)) {
//         return false;
//     }

//     return is_a($value, Enum::class, true) || enum_exists(get_class($value));
// }

// function dd($input, bool $terminate = true): string {
//     $backtrace = array_map(function($v) {
//         return $v["file"] . ":" . $v["line"];
        
//     }, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1));

//     $output = print_r($input, true);
//     $output .= "\n\n" . implode("\n", $backtrace);

//     if ($terminate) {
//         echo "\n" . $output  . "\n\n";
//         exit;
//     }

//     return $output;
// }

// function pd($input, bool $terminate = true): string {
//     $backtrace = array_map(function($v) {
//         return $v["file"] . ":" . $v["line"];
        
//     }, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1));

//     $output = "<pre>";
//     $output .= print_r($input, true);
//     $output .= "\n\n" . implode("\n", $backtrace);

//     if ($terminate) {
//         echo "\n" . $output  . "\n\n" . "</pre>";

//         exit;
//     }

//     return $output . "</pre>";
// }

// function toArray($value, $key = null): array {
//     $arrayValue = [];

//     if ($value !== null) {
//         if (is_array($value)) {
//             $value = array_map(function ($key, $value) {
//                 return toArray($value, $key);
//             }, array_keys($value), $value);
//         } else if (is_object($value)) {
//             if ($value instanceof IToArray) {
//                 $value = $value->toArray();
//             } else if ($value instanceof IToString) {
//                 $value = $value->toString();
//             } else {
//                 throw new \Exception("Object does not implement IToArray");
//             }
//         }
//     }

//     if ($key !== null) {
//         $arrayValue[$key] = $value;
//     } else {
//         $arrayValue = [$value];
//     }

//     return $arrayValue;
// }

// function fromArray(array $array, $key = null): object {
//     throw new \Exception("Not implemented");
// }

// function toSourceCode($value): string {
//     if ($value === null) {
//         return "null";
//     }
// }

// function validate_string($value, int $minLength, int $maxLength) {
//     if (!is_string($value)) {
//         throw new \InvalidArgumentException("Value must be a string.");
//     } else if (strlen($value) < $minLength) {
//         return "cannot be shorter than $minLength characters.";
//     } else if (strlen($value) > $maxLength) {
//         return "cannot be longer than $maxLength characters.";
//     }

//     return true;
// }

// function validate_int($value, int $minValue, int $maxValue) {
//     if (!is_int($value)) {
//         throw new \InvalidArgumentException("Value must be an integer.");
//     } else if ($value < $minValue || $value > $maxValue) {
//         return "must be between $minValue and $maxValue.";
//     }

//     return true;
// }