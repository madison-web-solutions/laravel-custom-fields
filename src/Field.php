<?php

namespace MadisonSolutions\LCF;

use MadisonSolutions\Generics\TypeHelper;
use Illuminate\Support\MessageBag;
use Validator;
use JsonSerializable;

abstract class Field implements JsonSerializable
{
    const COERCE_FAIL_LOG = 1;
    const COERCE_FAIL_THROW = 2;

    protected $store_in_json = false;

    public static function isFieldRule()
    {
        return function ($attribute, $value, $fail) {
            if (! ($value instanceof Field)) {
                $fail("{$attribute} must be a field");
            }
        };
    }

    public static function simpleStringKeysRule(...$reserved)
    {
        return function ($attribute, $value, $fail) use ($reserved) {
            if (is_array($value)) {
                foreach ($value as $key => $dummy) {
                    if (! preg_match('/^[_a-z][_a-z0-9]+$/', $key)) {
                        $fail("Invalid key '{$key}' in {$attribute} - keys must be simple snake_case strings");
                    }
                    if (in_array($key, $reserved)) {
                        $fail("Reserved word '{$key}' not allowed as key in {$attribute} - keys must be simple snake_case strings");
                    }
                }
            }
        };
    }

    protected $options = [];

    public function __construct(array $options)
    {
        $this->options = $options + $this->optionDefaults();
        Validator::make($this->options, $this->optionRules())->validate();
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }
    }

    public function optionDefaults() : array
    {
        return [
            'required' => false,
            'help' => null,
            'default' => null,
        ];
    }

    public function optionRules() : array
    {
        return [
            'required' => 'required|boolean',
            'help' => 'nullable|string',
            'default' => 'nullable',
        ];
    }

    protected function log($msg, ...$args)
    {
        error_log(get_class($this) . " - $msg");
        foreach ($args as $arg) {
            error_log(print_r($arg, true));
        }
    }

    public function fieldTypeName() : string
    {
        return kebab_case(preg_replace('/Field$/', '', class_basename($this)));
    }

    public function inputComponent() : string
    {
        return 'text-input';
    }

    public function jsonSerialize()
    {
        return [
            'type' => $this->fieldTypeName(),
            'inputComponent' => $this->inputComponent(),
            'options' => $this->options,
        ];
    }

    /**
     * Take a value and try to convert it into the right type for this field
     * May be the right type already, or may be a 'primitive' representation
     * Returns the converted value on success
     * Behaviour on failure determined by 3rd param $on_fail which is an options bitmask
     */
    public function coerce($input, int $on_fail = self::COERCE_FAIL_THROW)
    {
        $this->doCoerce($input, $output, $on_fail);
        return $output;
    }

    /**
     * Take a value and try to convert it into the right type for this field
     * May be the right type already, or may be a 'primitive' representation
     */
    protected function doCoerce($input, &$output, int $on_fail) : bool
    {
        $output = null;
        if (is_null($input)) {
            return true;
        }
        if ($this->coerceNotNull($input, $output, $on_fail)) {
            return true;
        }
        $msg = "Failed to coerce value ".json_encode($input)." to ".get_class($this);
        if ($on_fail & self::COERCE_FAIL_LOG) {
            $this->log($msg, $input);
        }
        if ($on_fail & self::COERCE_FAIL_THROW) {
            throw new \TypeError($msg);
        }
        return false;
    }

    /**
     * Take a value of any type, but guaranteed not to be null, and try to convert it to the right type for this field
     * Return true, and store the coerced value in $output if the coersion succeeds, return false otherwise
     */
    abstract protected function coerceNotNull($input, &$output, int $on_fail) : bool;

    /**
     * Check that a value is the right type for this field, with no conversions
     * Throw a TypeError if it's not the right type
     */
    public function assertType($input)
    {
        if (! $this->testType($input)) {
            throw new \TypeError("Value ".json_encode($input)." is not correct type for ".get_class($this));
        }
    }

    protected function testType($input) : bool
    {
        if (is_null($input)) {
            return true;
        } else {
            return $this->testTypeNotNull($input);
        }
    }

    /**
     * Take a value of any type, but guaranteed not to be null
     * and check it's the right type for this field with no conversions
     * Return true if it's the right type, false otherwise
     */
    abstract protected function testTypeNotNull($input) : bool;

    /**
     * Take a raw value from the database and deserialize if necessary
     * Return a value which can be coerced into the right type for this field
     * Return null and log an error if the deserialzation fails - don't throw errors
     */
    protected function databaseDeserialize($db_value)
    {
        if (is_null($db_value)) {
            return null;
        } else {
            return $this->databaseDeserializeNotNull($db_value);
        }
    }

    /**
     * Take a raw value from the database which is guaranteed not to be null and deserialize if necessary
     * Return a value which can be coerced into the right type for this field
     * Return null and log an error if the deserialzation fails - don't throw errors
     */
    protected function databaseDeserializeNotNull($db_value)
    {
        if ($this->store_in_json) {
            if (! Coerce::toString($db_value, $db_str_value)) {
                $this->log("Deserialize failure - db value could not be coerced to string", $db_value);
            }
            $primitive_value = json_decode($db_str_value, true);
            if (json_last_error() == JSON_ERROR_NONE) {
                return $primitive_value;
            } else {
                $msg = json_last_error_msg();
                $this->log("Deserialize failure - invalid json - {$msg}", $db_str_value);
                return null;
            }
        } else {
            return $db_value;
        }
    }

    /**
     * Take a value guaranteed to be the correct primitive representation for this field, and guaranteed not to be null
     * and return the representation of it suitable for storage in a database - should never fail
     */
    protected function databaseSerializeNotNull($primitive_value)
    {
        if ($this->store_in_json) {
            return json_encode($primitive_value);
        } else {
            return $primitive_value;
        }
    }

    public function defaultValue()
    {
        return $this->coerce($this->options['default'] ?? null, self::COERCE_FAIL_LOG);
    }

    /**
     * Take a raw value from the database and try to convert it to the right type for this field
     * Possibly involves some kind of deserializing
     * If the conversion fails, log an error and return null - don't throw errors
     */
    public function fromDatabase($db_value)
    {
        $primitive_value = $this->databaseDeserialize($db_value);
        return $this->coerce($primitive_value, self::COERCE_FAIL_LOG);
    }

    /**
     * Take a value and coerce it to the right type for this field, then obtain the 'primitive' representation of it
     * This could then be used on the front-end via json
     * Throws TypeError if the conversion fails
     */
    public function toPrimitive($value)
    {
        $cast_value = $this->coerce($value);
        if (is_null($cast_value)) {
            return null;
        }
        return $this->toPrimitiveNotNull($cast_value);
    }

    /**
     * Take a value guaranteed to be the right type for this field, and guaranteed not to be null
     * and return the 'primitive' representation of it - should never fail
     * Default is to return the value unchanged - thus assuming that the correct type for the field is already 'primitive'
     */
    protected function toPrimitiveNotNull($cast_value)
    {
        return $cast_value;
    }

    /**
     * Take a value and coerce it to the right type for this field, then convert to raw data to be inserted into a database
     */
    public function toDatabase($value)
    {
        $primitive_value = $this->toPrimitive($value);
        if (is_null($primitive_value)) {
            return null;
        } else {
            return $this->databaseSerializeNotNull($primitive_value);
        }
    }


    public function validate(array $data, string $path, MessageBag $messages)
    {
        $validator = Validator::make($data, [
            $path => $this->getValidationRules()
        ]);
        $messages->merge($validator->messages());
    }

    public function getValidationRules()
    {
        $rules = [];
        if ($this->options['required']) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        return $rules;
    }

    public function getSubField(string $key)
    {
        return null;
    }
}
