<?php

// declare(strict_types=1);

// namespace Pst\Core;

// class Debugger {
//     // Pure static class
//     private function __construct() {}

//     private static function indentString(int $level) {
//         return str_repeat("  ", $level);
//     }

//     private static function debugObject(object $object) {
//         // $output = [];

//         // $reflection = new \ReflectionObject($object);
//         // $properties = $reflection->getProperties();

//         // foreach ($properties as $property) {
//         //     $property->setAccessible(true);
//         //     $output[$property->getName()] = self::debugInput($property->getValue($object));
//         // }

//         // return $output;
//     }

//     private static function debugInput($input, $key = null): array {
//         $outputType = gettype($input);
//         $output = null;

//         if (is_string($input)) {
//             $output = "'" . $input . "'";
//         } else if (is_int($input)) {
//             $output = (string) $input;
//         } else if (is_float($input)) {
//             $output = number_format($input, 4);
//         } else if (is_bool($input)) {
//             $output = $input ? 'true' : 'false';
//         } else if (is_null($input)) {
//             $output = 'null';
//         } else if (is_array($input)) {
//             $output = array_reduce(array_keys($input), function($carry, $key) use ($input) {
//                 $carry = array_merge($carry, self::debugInput($input[$key], $key));
//                 return $carry;
//             }, []);

            


//         } else if (is_object($input)) {
//             //$output = self::debugObject($input);
//         } else {
//             $output = $input;
//         }

//         return $key === null ? [$outputType => $output] : [$key => [$outputType => $output]];
//     }

//     private static function arrayToString(array $input, int $depth = 0): string {
//         $mappedInput = array_map(function($key, $value) use ($depth) {
//             $keyOutput = is_int($key) ? "" : "'" . $key . "'";
//             $output = self::indentString($depth) . $keyOutput . " => ";

//             if (is_array($value)) {
//                 $output .= trim(self::debugArrayToString($value, $depth + 1));
//             } else {
//                 $output .= trim($value) . "\n";
//             }
            
//             return rtrim($output);
//         }, array_keys($input), array_values($input));

//         return implode(",\n", $mappedInput) . "\n";
//     }

//     private static function debugArrayToString(array $input, int $depth = 0): string {
//         $output = self::indentString($depth);

//         foreach ($input as $key => $value) {
//             if ($key === "array") {
//                 $output .= "[\n" . rtrim(self::arrayToString($value, $depth+1)) . "\n" . self::indentString($depth) . "]\n";
//             } else if (in_array($key, ["string", "integer", "double", "boolean", "NULL"])) {
//                 $output .= $value;
//             } else {
//                 echo "key: $key, value: $value\n";
//                 exit;
//             }
//             // echo "key: $key, value: $value\n";
//             // exit;
//             // $output .= str_repeat("  ", $depth) . $key . ": ";

//             // if (is_array($value)) {
//             //     $output .= "\n" . self::debugArrayToString($value, $depth + 1);
//             // } else {
//             //     $output .= $value . "\n";
//             // }
//         }

//         return $output;
//     }

//     public static function debug($input) {
//         $debugArray = self::debugInput($input);
//         print_r($debugArray);

//         return self::debugArrayToString($debugArray);
//     }
// }