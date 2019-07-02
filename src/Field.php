<?php

namespace MadisonSolutions\LCF;

use Log;
use JsonSerializable;
use Validator as LaravelValidator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use MadisonSolutions\Coerce\Coerce;

/**
 * Base class for all LCF fields
 */
abstract class Field implements JsonSerializable
{
    /**
     * Whether to convert this field's values into json by default for database storage
     *
     * @var bool
     */
    protected $store_in_json = false;

    /**
     * Array of options which customize the field's behaviour
     *
     * @var array
     */
    protected $options = [];

    /**
     * Utility function for getting a validation rule that checks the value in question is an instance of Field
     *
     * @return callable The rule function
     */
    public static function isFieldRule()
    {
        return function ($attribute, $value, $fail) {
            if (! ($value instanceof Field)) {
                $fail("{$attribute} must be a field");
            }
        };
    }

    /**
     * Utility function for getting a valudation rule that checks the value in question can be used as a field key
     *
     * Specifically, field keys should be alphanumeric, and lower case with words separated by underscores.
     * Also numbers are not allowed as the first character.
     *
     * @param string $reserved,... Optional additional strings to disallow
     * @return callable The rule function
     */
    public static function simpleStringKeysRule(...$reserved)
    {
        return function ($attribute, $value, $fail) use ($reserved) {
            if (is_array($value)) {
                foreach ($value as $key => $dummy) {
                    if (! preg_match('/^[_a-z][_a-z0-9]*$/', $key)) {
                        $fail("Invalid key '{$key}' in {$attribute} - keys must be simple snake_case strings");
                    }
                    if (in_array($key, $reserved)) {
                        $fail("Reserved word '{$key}' not allowed as key in {$attribute} - keys must be simple snake_case strings");
                    }
                }
            }
        };
    }

    /**
     * Create a new instance of the Field
     *
     * @param array $options Array of options for customizing the field's behaviour - available options depend on the specific Field type.
     * @throws FieldOptionsValidationException If the supplied options are not valid for the specific Field.
     */
    public function __construct(array $options)
    {
        $this->options = $options + $this->optionDefaults();
        $validator = LaravelValidator::make($this->options, $this->optionRules());
        if (! $validator->passes()) {
            throw new FieldOptionsValidationException("Failed to create LCF " . get_class($this) . " - invalid definition - " . json_encode($validator->errors()));
        }
    }

    /**
     * Getter function
     *
     * By default a Field's options can be accessed as if they were properties on the Field object
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }
    }

    /**
     * Get default values for the field's options
     *
     * Child classes should override this if they want to provide default values for their own options
     *
     * @return array Array of default option values
     */
    public function optionDefaults() : array
    {
        return [
            'required' => false,
            'help' => null,
            'default' => null,
            'condition' => null,
        ];
    }

    /**
     * Get validation rules for the field's options
     *
     * Options are validated when an instance of the Field is created.
     * The standard Laravel validation methods are used.
     * Child classes should override this to enforce their own rules about possible option values
     *
     * @return array Array of option rules (in format used by Laravel validators)
     */
    public function optionRules() : array
    {
        return [
            'required' => 'required|boolean',
            'help' => 'nullable|string',
            'default' => 'nullable',
            'css_classes' => 'nullable|array',
            'css_classes.*' => 'required|string',
            'condition' => 'nullable|array',
            'condition.0' => 'required_with:condition|in:eq,in',
            'condition.1' => 'required_with:condition|string',
        ];
    }

    /**
     * Return a kebab-case version of this field's type
     */
    public function fieldTypeName() : string
    {
        return kebab_case(preg_replace('/Field$/', '', class_basename($this)));
    }

    /**
     * Get the vue component used for rendering this field in the browser
     *
     * Note that rendering a field requires an outer 'field' component and an inner 'input' component.
     * This function specifies the outer 'field' component.
     *
     * @return string The vue component name
     */
    abstract public function fieldComponent() : string;

    /**
     * Get the vue component use for rendering this field's input in the browser
     *
     * Note that rendering a field requires an outer 'field' component and an inner 'input' component.
     * This function specifies the inner 'input' component.
     * If null is returned then the default input component is a plain text input.
     *
     * @return ?string The vue component name, or null
     */
    public function inputComponent() : ?string
    {
        return null;
    }

    /**
     * Get the JSON data for this field
     *
     * Note that this is not intended to be used as a general purpose serialization function.
     * Specifically, this is the data that will be passed to the front end vue component to render the field.
     * The fieldComponent entry specifies the outer vue component.
     * The inputComponent entry specifies the inner vue component.
     * The settings entry specifies props passed to the vue components for customizing the behaviour.
     * Note that setting names are camelCased by default as per normal convention for variables in JS.
     *
     * Child components may need to modify the contents of the settings entry in the output array.
     *
     * @return array Data which will be converted to JSON and passed to the front end to render the field
     */
    public function jsonSerialize()
    {
        $data = [
            'fieldComponent' => $this->fieldComponent(),
            'inputComponent' => $this->inputComponent(),
            'settings' => ['type' => $this->fieldTypeName()],
        ];
        foreach ($this->options as $key => $value) {
            // camelCase option names as per convention in JS
            $data['settings'][Str::camel($key)] = $value;
        }
        return $data;
    }

    /**
     * Get this field's default value
     *
     * @return mixed The field's default value, coerced to the right type, or null if there is no default
     */
    public function defaultValue()
    {
        $this->coerce($this->options['default'] ?? null, $output);
        return $output;
    }

    /**
     * Coerce a value to the right type for this field
     *
     * Take a value of potentially any type, and try to convert it into the right type for this field.
     * Certain fields may also apply input 'transformations' to the value, such as a text field forcing a value to lower case.
     * The coerced and transformed value is stored in $output
     * Returns boolean true if coercion (including all nested sub-fields) was successful, false otherwise.
     *
     * Generally coercion should only succeed if the input value can be coerced without any ambiguity into
     * this field's target type. Note that for complex fields which have nested sub-fields, partial coercion
     * will be performed. Eg for a repeater, if some of the repeated values are coercable, and some not, then $output
     * will not be null, it will be an array and it will contain the values that could be coerced.
     *
     * If coercion fails, the contents of $output depend on the 3rd parameter $keep_invalid.  If true, then uncoercable
     * values from $input will be passed into $output. If false then uncoercable values will be replaced with null.
     *
     * For example:
     * ```
     *   $integer_field->coerce('foo', $output, false); // $ouput is null
     *   $integer_field->coerce('foo', $output, true); // $output is 'foo'
     *   $repeater_of_integers->coerce([10, '20', 'foo'], $output, false); // $output is [10, 20, null]
     *   $repeater_of_integers->coerce([10, '20', 'foo'], $output, true); // $output is [10, 20, 'foo']
     * ```
     *
     * Note also that null must always be considered as being the right 'type' for all fields,
     * and that empty strings are always converted to null.
     * This makes working with HTML forms simpler, since that can only submit strings, and so an empty string is the
     * only reasonable way of representing null.
     *
     * Child classes should normally override coerceNotNull() instead of coerce() to take advantage of standardized null-handling
     *
     * @param mixed $input The input value
     * @param mixed &$output The coerced value will be stored in this variable
     * @param bool $keep_invalid Whether to keep uncoercable values or replace them with null, default false
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
     * Coerce a value which is not null to the right type for this field
     *
     * It is intended that child classes would normally override this function rather than overriding coerce(),
     * because the default handling of nulls would not normally change.
     *
     * @param mixed $input The input value
     * @param mixed &$output The coerced value will be stored in this variable
     * @param bool $keep_invalid Whether to keep uncoercable values or replace them with null, default false
     */
    abstract protected function coerceNotNull($input, &$output, bool $keep_invalid) : bool;

    /**
     * Apply input transformations to a coerced value
     *
     * Apply any transformations which are required by this particular field.
     * This is performed after coercion, so it can be assumed that the value is the correct type for the field.
     * For example, a text field might require all values to be lower case.
     * This would be the correct place to apply the case folding tranformation.
     *
     * @param mixed $cast_value The already-coerced input value
     * @return mixed The value after any required transformations
     */
    protected function applyInputTransformationsNotNull($cast_value)
    {
        return $cast_value;
    }

    /**
     * Take a raw value from the database and try to convert it to the right type for this field
     *
     * This function would be called instead of coerce() when the input value comes from a database or similar.
     * It provides fields with an opportunity to attempt some kind deserialization of the data prior to coercion.
     *
     * @param mixed $db_value Raw value from the database
     * @return mixed Coerced (or partially coerced) value
     */
    public function fromDatabase($db_value)
    {
        $primitive_value = $this->databaseDeserialize($db_value);
        $this->coerce($primitive_value, $output);
        return $output;
    }

    /**
     * Convert a value into format suitable for saving in a database
     *
     * Take a value, of potentially any type, and attempt to coerce it to the right type for this field
     * Then perform any neccesary serialization so that the value can then be inserted into a database
     *
     * Child classes should normally override databaseSerializeNotNull() instead of toDatabase() to take advantage of standardized null-handling
     *
     * @param mixed $value Value to be coerced
     * @return mixed Coerced (or partially coerced) value serialized for insertion into a database
     */
    public function toDatabase($value)
    {
        $this->coerce($value, $cast_value);
        return is_null($cast_value) ? null : $this->databaseSerializeNotNull($cast_value);
    }

    /**
     * Take a raw value from the database and deserialize if necessary
     *
     * Child classes should normally override databaseDeserializeNotNull() instead of databaseDeserialize() to take advantage of standardized null-handling
     *
     * @param mixed $db_value Raw value from the database
     * @return mixed Value which can be coerced into the right type for this field, or null if deserialzation fails
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
     *
     * @param mixed $db_value Raw value from the database
     * @return mixed Value which can be coerced into the right type for this field, or null if deserialzation fails
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
     * Convert a value (of the correct type for this field) into format suitable for saving in a database
     *
     * Take a value guaranteed to be the correct type for this field, and guaranteed not to be null
     * and return the representation of it suitable for storage in a database.
     * This should be a safe operation which never fails.
     *
     * @param mixed $cast_value Value to be serialized
     * @return mixed Value serialized for insertion into a database
     */
    protected function databaseSerializeNotNull($cast_value)
    {
        if ($this->store_in_json) {
            return json_encode($cast_value);
        } else {
            return $cast_value;
        }
    }

    /**
     * Validate the given value
     *
     * Checks whether the given value is considered valid (possibly in conjunction with other
     * submitted data), and writes any validation error messages to the supplied $messages array.
     *
     * All fields have a 'condition' option which allows a user to specify the conditions under which the field should be present in the forms.
     * If the condition check is negative, then validation for this field will not be performed.
     *
     * Note that this function is not intended to be called by external code - it would normally only be called from within a Validator instance
     *
     * The $path parameter is required for keying any reported error messages, and also for testing relative data conditions.
     * The $validator parameter is required for accessing relative data values.
     *
     * Child fields should normally override the validateNotNull function rather than validate to take advantage of default null handling.
     *
     * Note that validation is usually carried out after coercion, but $value cannot be assumed to be the right type, because coercion might have failed.
     *
     * @param string $path The location of this value within the tree of submitted data (in 'dot notation')
     * @param mixed $value The value being tested
     * @param ?array &$messages Array into which error messages are written
     * @param Validator $validator Validator object providing access to other submitted data
     * @return void Nothing is returned, the overall decision about whether data is valid depends on the presence or not of error messages
     */
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
                $messages[$path][] = $this->trans('required');
            }
            return;
        }
        return $this->validateNotNull($path, $value, $messages, $validator);
    }

    /**
     * Validate the given value, guaranteed not to be null
     *
     * @param string $path The location of this value within the tree of submitted data (in 'dot notation')
     * @param mixed $value The value being tested
     * @param ?array &$messages Array into which error messages are written
     * @param Validator $validator Validator object providing access to other submitted data
     * @return void Nothing is returned, the overall decision about whether data is valid depends on the presence or not of error messages
     */
    public function validateNotNull(string $path, $cast_value, &$messages, ?Validator $validator = null)
    {
        //
    }

    /**
     * Translate a validation message
     *
     * Return a validation message after being translated to the local language.
     * Uses the standard Laravel __() function, and assumes lang files with the lcf:: namespace
     *
     * @param string $msg_code The validation code as set by the validate method, eg 'required'
     * @param array $params Optional array of parameters to substitute into message placeholders
     */
    public function trans(string $msg_code, array $params = [])
    {
        $field_code = $this->fieldTypeName();
        return __("lcf::validation.{$field_code}.{$msg_code}", $params);
    }

    /**
     * Resolve a relative path
     *
     * Given a relative path in 'dot notation', which is relative to the supplied absolute base path,
     * Calculate the resulting absolute path.
     * Caret characters (^) at the start of the relative path mean 'go up one level'
     *
     * @param string $base_path Absolute base path in 'dot notation'
     * @param string $relative_path Path in 'dot notation' which is relative to the base path
     * @return string Absolute path in 'dot notation'
     */
    protected function resolveRelativePath(string $base_path, string $relative_path)
    {
        $base_path = explode('.', $base_path);
        while ($relative_path[0] === '^') {
            array_pop($base_path);
            $relative_path = substr($relative_path, 1);
        }
        $path = array_merge($base_path, $relative_path ? explode('.', $relative_path) : []);
        return implode('.', $path);
    }

    /**
     * Test whether this field's relative data condition is satisfied
     *
     * If this field has a relative data condition, test whether $data satisfies the condition
     * For example, some fields are set up to only display if a particular other field has a particular value
     *
     * @param string $path The path to this field's data in 'dot notation'
     * @param array $data Array containing all of the submitted data
     * @return bool True if the condition is satisfied (or there is no condition on this field), false otherwise
     */
    protected function testCondition(string $path, array $data)
    {
        if (! $this->condition) {
            return true;
        }
        return $this->doTestCondition($this->condition, $path, $data);
    }

    /**
     * Test whether the given data condition is satisfied
     *
     * @param array $condition Array containing the specification of the condition
     * @param string $path The path to this field's data in 'dot notation'
     * @param array $data Array containing all of the submitted data
     * @return bool True if the condition is satisfied, false otherwise
     */
    protected function doTestCondition(array $condition, string $path, array $data)
    {
        $condition_type = array_shift($condition);

        /* maybe coming soon
        if ($condition_type == 'and') {
            foreach ($condition as $sub_condition) {
                if (! $this->doTestCondition($sub_condition, $path, $data)) {
                    return false;
                }
            }
            return true;
        }

        if ($condition_type == 'or') {
            foreach ($condition as $sub_condition) {
                if ($this->doTestCondition($sub_condition, $path, $data)) {
                    return true;
                }
            }
            return false;
        }

        if ($condition_type == 'not') {
            $negated_condition = $condition[0];
            return ! $this->doTestCondition($negated_condition, $path, $data);
        }
        */

        $relative_path = $condition[0];
        $test_value = $condition[1] ?? null;
        $other_field_path = $this->resolveRelativePath($path, '^' . $relative_path);
        $other_field_value = data_get($data, $other_field_path);

        switch ($condition_type) {
            case 'eq':
                return $other_field_value === $test_value;
            case 'in':
                return in_array($other_field_value, $test_value);
        }
        return true;
    }

    /**
     * Return the sub-field of this field, at the specified key
     *
     * Note this is only relevant for complex fields with nested sub-fields
     * For scalar fields, this function always returns null
     *
     * @param string $key The sub-field key
     * @return ?Field The sub-field, or null if there is no sub-field for the specified key
     */
    public function getSubField(string $key)
    {
        return null;
    }

    /**
     * Apply the specified callback function to the given value and any nested sub values
     *
     * The callback function will be called for the given value and recursively for all nested sub values.
     * Provides a way to perform some action at every point of a value-tree.
     * The callback will be called with 3 arguments:
     *  1. The field (or sub-field)
     *  2. The value (or sub-value)
     *  3. The path to the value as an array of path components (empty for the outermost field)
     * Note that the value will be coerced to the right type for this field before starting to apply the callback
     * For complex fields, the callback will be applied to outer fields before sub-fields
     *
     * Child classes should override doWalk() instead of walk()
     *
     * @param callable $vallback The callback function
     * @param mixed $value The value to apply the callback function to
     * @return void
     */
    public function walk(callable $callback, $value)
    {
        $this->coerce($value, $cast_value);
        $this->doWalk($callback, $cast_value, []);
    }

    /**
     * Internal function for applying a walk() callback
     *
     * @param callable $vallback The callback function
     * @param mixed $cast_value The value to apply the callback function to, already coerced to the right type
     * @param array $path Array of path components so far
     * @return void
     */
    protected function doWalk(callable $callback, $cast_value, array $path)
    {
        $callback($this, $cast_value, $path);
    }

    /**
     * Transform a value-tree by applying the specified callback function to the given value and any nested sub values
     *
     * The callback function will be called for the given value and recursively for all nested sub values.
     * Provides a way to perform a transformation at every point of a value-tree.
     * The callback will be called with 3 arguments:
     *  1. The field (or sub-field)
     *  2. The value (or sub-value)
     *  3. The path to the value as an array of path components (empty for the outermost field)
     * Note that the value will be coerced to the right type for this field before starting to apply the callback
     * For complex fields, the callback will be applied to sub-fields before outer-fields - IE the deepest parts of the tree first
     *
     * Child classes should override doMap() instead of map()
     *
     * @param callable $vallback The callback function
     * @param mixed $value The value to apply the callback function to
     * @return mixed The transformed value
     */
    public function map(callable $callback, $value)
    {
        $this->coerce($value, $cast_value);
        return $this->doMap($callback, $cast_value, []);
    }

    /**
     * Internal function for applying a map() callback
     *
     * @param callable $vallback The callback function
     * @param mixed $cast_value The value to apply the callback function to, already coerced to the right type
     * @param array $path Array of path components so far
     * @return mixed The transformed value
     */
    protected function doMap(callable $callback, $cast_value, array $path)
    {
        return $callback($this, $cast_value, $path);
    }

    /**
     * Transform a value by 'expanding' it with suitable supplementary data
     *
     * What exactly the 'expanded' data contains depends on the particular field.
     * For example, 'expanding' a ModelIdField would replace the id value with the full model instance loaded from the database
     * For example, 'expanding' a MediaIdField would replace the id value with a MediaItem instance.
     * Other fields may provide other types of expansion suitable to their data types.
     *
     * Note that path keys may also be transformed when expanding. By default ModelIdField and MediaIdField change their keys
     * By removing an '_id' suffix.  So for example user_id => 10 would be expanded to user => User::find(10)
     *
     * Expansion is done recursively for nested sub-fields. To try and make it as efficient as possible, expansion is performed in
     * 2 stages. A 'expandPrepare' stage, where fields can figure out what data will need to be fetched, and a 'doExpand' stage
     * where the expanded values are subsituted into the value tree. This allows, for example ModelIdField to fetch multiple models
     * with a single database query.
     *
     * The supplied value is coerced to the right data type for this field before expanding.
     *
     * Child classes should usually override the expandPrepareNotNull(), doExpandNotNull() and expandKey() methods,
     * instead of the expand(), expandPrepare() and doExpand() methods
     *
     * @param mixed $value The value to expand
     * @return mixed $value The value after expansion
     */
    public function expand($value)
    {
        $this->coerce($value, $cast_value);
        $this->expandPrepare($cast_value);
        return $this->doExpand($cast_value);
    }

    /**
     * Perform any required preparation before expanding
     *
     * @param mixed $cast_value The value after coercion to the correct type
     * @return void
     */
    protected function expandPrepare($cast_value)
    {
        if (! is_null($cast_value)) {
            $this->expandPrepareNotNull($cast_value);
        }
    }

    /**
     * Perform any required preparation before expanding, after checking for null values
     *
     * @param mixed $cast_value The value after coercion to the correct type, guaranteed not to be null
     * @return void
     */
    protected function expandPrepareNotNull($cast_value)
    {
        return;
    }

    /**
     * Do the value expansion
     *
     * @param mixed $cast_value The value after coercion to the correct type
     * @return mixed The 'expanded' value
     */
    protected function doExpand($cast_value)
    {
        return is_null($cast_value) ? null : $this->doExpandNotNull($cast_value);
    }

    /**
     * Do the value expansion, after checking for null values
     *
     * @param mixed $cast_value The value after coercion to the correct type, guaranteed not to be null
     * @return mixed The 'expanded' value
     */
    protected function doExpandNotNull($cast_value)
    {
        return $cast_value;
    }

    /**
     * Perform any required transformations of the key
     *
     * @param string $key The original key
     * @return string The key that the expanded value should have
     */
    protected function expandKey(string $key)
    {
        return $key;
    }
}
