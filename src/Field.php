<?php

namespace MadisonSolutions\LCF;

use Log;
use JsonSerializable;
use Validator as LaravelValidator;
use Illuminate\Support\MessageBag;
use MadisonSolutions\Coerce\Coerce;

abstract class Field implements JsonSerializable
{
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
        $validator = LaravelValidator::make($this->options, $this->optionRules());
        if (! $validator->passes()) {
            throw new \Exception("Failed to create LCF " . get_class($this) . " - invalid definition - " . json_encode($validator->errors()));
        }
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
            'condition' => null,
        ];
    }

    public function optionRules() : array
    {
        return [
            'required' => 'required|boolean',
            'help' => 'nullable|string',
            'default' => 'nullable',
            'condition' => 'nullable|array',
            'condition.0' => 'required_with:condition|in:eq,in',
            'condition.1' => 'required_with:condition|string',
        ];
    }

    public function fieldTypeName() : string
    {
        return kebab_case(preg_replace('/Field$/', '', class_basename($this)));
    }

    abstract public function fieldComponent() : string;

    public function inputComponent() : ?string
    {
        return null;
    }

    public function jsonSerialize()
    {
        return [
            'fieldComponent' => $this->fieldComponent(),
            'inputComponent' => $this->inputComponent(),
            'settings' => array_merge($this->options, ['type' => $this->fieldTypeName()]),
        ];
    }

    public function defaultValue()
    {
        $this->coerce($this->options['default'] ?? null, $output);
        return $output;
    }

    /**
     * Take a value and try to convert it into the right type for this field
     * May be the right type already, or may be a 'primitive' representation
     * The coerced value is stored in $output
     * Returns boolean true if coercion was successful, false otherwise
     */
    public function coerce($input, &$output, bool $keep_invalid = false) : bool
    {
        // The eventual value of $output should depend only on $input,
        // not whatever it happened to be set to outside of this function
        // So start by erasing any previous value of $output
        $output = null;
        // All fields should accept a null value, and the empty string is always converted to null
        if (is_null($input) || $input === '') {
            return true;
        }
        if ($this->coerceNotNull($input, $output, $keep_invalid)) {
            $output = $this->applyInputTransformationsNotNull($output);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Take a value of any type, but guaranteed not to be null, and try to convert it to the right type for this field
     * Return true, and store the coerced value in $output if the coersion succeeds, return false otherwise
     * $output should always be set to a value which is valid for the field type, even if coersion fails
     * For a single valued field, if coersion fails, then $output should normally be set to null (which is always a valid value for any field)
     * For complex fields with nested sub-fields, if coersion of an inner field fails, then coersion of the outer field must also be
     * deemed to have failed, and the function should return null, however $output might still contain values from other sub-fields which
     * were valid.
     */
    abstract protected function coerceNotNull($input, &$output, bool $keep_invalid) : bool;

    protected function applyInputTransformationsNotNull($cast_value)
    {
        return $cast_value;
    }

    /**
     * Take a raw value from the database and try to convert it to the right type for this field
     * Possibly involves some kind of deserializing
     * If the conversion fails, log an error and return null - don't throw errors
     */
    public function fromDatabase($db_value)
    {
        $primitive_value = $this->databaseDeserialize($db_value);
        $this->coerce($primitive_value, $output);
        return $output;
    }

    /**
     * Take a value and coerce it to the right type for this field, then convert to raw data to be inserted into a database
     */
    public function toDatabase($value)
    {
        $this->coerce($value, $cast_value);
        return is_null($cast_value) ? null : $this->databaseSerializeNotNull($cast_value);
    }

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
                Log::error("Deserialize failure - db value could not be coerced to string", ['db_value' => $db_value]);
            }
            $primitive_value = json_decode($db_str_value, true);
            if (json_last_error() == JSON_ERROR_NONE) {
                return $primitive_value;
            } else {
                $msg = json_last_error_msg();
                Log::error("Deserialize failure - invalid json - {$msg}", ['db_str_value' => $db_str_value]);
                return null;
            }
        } else {
            return $db_value;
        }
    }

    /**
     * Take a value guaranteed to be the correct type for this field, and guaranteed not to be null
     * and return the representation of it suitable for storage in a database - should never fail
     */
    protected function databaseSerializeNotNull($cast_value)
    {
        if ($this->store_in_json) {
            return json_encode($cast_value);
        } else {
            return $cast_value;
        }
    }

    public function validate(string $path, $value, &$messages, Validator $validator)
    {
        if (is_null($messages)) {
            $messages = [];
        }
        if ($this->testCondition($path, $validator->getData()) === false) {
            return;
        }
        if (is_null($value)) {
            if ($this->options['required']) {
                $messages[$path][] = "This field is required";
            }
            return;
        }
        return $this->validateNotNull($path, $value, $messages, $validator);
    }

    public function validateNotNull(string $path, $cast_value, &$messages, ?Validator $validator = null)
    {
        //
    }

    // Given a relative path in 'dot notation', which is relative to the supplied absolute base path,
    // Calculate the resulting absolute path.
    // Caret characters (^) at the start of the relative path mean 'go up one level'
    protected function resolveRelativePath(string $basePath, string $relativePath)
    {
        $basePath = explode('.', $basePath);
        while ($relativePath[0] === '^') {
            array_pop($basePath);
            $relativePath = substr($relativePath, 1);
        }
        return implode($basePath, '.') . ($relativePath ? '.' . $relativePath : '');
    }

    protected function testCondition(string $path, array $data)
    {
        if (! $this->condition) {
            return true;
        }

        $conditionType = $this->condition[0];
        $relativePath = $this->condition[1];
        $testValue = $this->condition[2] ?? null;
        $otherFieldPath = $this->resolveRelativePath($path, $relativePath);
        $otherFieldValue = data_get($data, $otherFieldPath);

        switch ($conditionType) {
            case 'eq':
                return $otherFieldValue === $testValue;
            case 'in':
                return in_array($otherFieldValue, $testValue);
        }
        return true;
    }

    public function getSubField(string $key)
    {
        return null;
    }

    public function walk(callable $callback, $value, ...$params)
    {
        $cast_value = $this->coerce($value);
        return $this->doWalk($callback, $cast_value, [], ...$params);
    }

    protected function doWalk(callable $callback, $cast_value, array $path, ...$params)
    {
        $callback($this, $cast_value, $path, ...$params);
    }
}
