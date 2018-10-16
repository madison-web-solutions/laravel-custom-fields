<?php
namespace MadisonSolutions\LCF;

class Coerce
{
    public static function toString($input, &$output) : bool
    {
        if (is_null($input)) {
            $output = '';
            return true;
        }
        if (is_bool($input)) {
            $output = $input ? 'true' : 'false';
            return true;
        }
        if (is_scalar($input)) {
            $output = (string) $input;
            return true;
        }
        if (is_object($input) && method_exists($input, '__toString')) {
            $output = $input->__toString();
            return true;
        }
        return false;
    }

    public static function toInt($input, &$output) : bool
    {
        if (is_int($input)) {
            $output = $input;
            return true;
        }
        if (is_bool($input)) {
            $output = $input ? 1 : 0;
            return true;
        }
        if (is_string($input) && is_numeric($input)) {
            $input = (float) $input;
        }
        if (is_float($input) && is_finite($input)) {
            $output = (int) $input;
            return ( $output == $input );
        }
        return false;
    }

    public static function toFloat($input, &$output) : bool
    {
        if (is_float($input)) {
            $output = $input;
            return true;
        }
        if (is_int($input)) {
            $output = floatval($input);
            return true;
        }
        if (is_bool($input)) {
            $output = floatval($input ? 1 : 0);
            return true;
        }
        if (is_string($input) && is_numeric($input)) {
            $output = floatval($input);
            return true;
        }
        return false;
    }

    public static function toArrayKey($input, &$output) : bool
    {
        if (! is_scalar($input)) {
            return false;
        }
        $dummy = [$input => true];
        $output = key($dummy);
        return true;
    }
}
