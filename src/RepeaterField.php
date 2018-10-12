<?php
namespace MadisonSolutions\LCF;

use MadisonSolutions\Generics\TypedList;
use Illuminate\Support\MessageBag;

class RepeaterField extends Field
{
    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->sub_field = $options['sub_field'];
    }

    public function optionDefaults() : array
    {
        return parent::optionDefaults();
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['sub_field'] = [Field::isFieldRule()];
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'lcf-repeater-input';
    }




    protected function testTypeNotNull($input) : bool
    {
        if (! is_array($input)) {
            return false;
        }
        foreach ($input as $value) {
            if (! $this->sub_field->testType($value)) {
                return false;
            }
        }
        return true;
    }

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
    {
        $ok = true;
        if (! is_array($input)) {
            $input = [$input];
        }
        if (count($input) == 0) {
            $output = null;
            return true;
        }
        $output = [];
        foreach ($input as $sub_input) {
            if (! $this->sub_field->doCoerce($sub_input, $sub_output, $on_fail)) {
                $ok = false;
            }
            $output[] = $sub_output;
        }
        return $ok;
    }

    protected function databaseDeserializeNotNull($db_value)
    {
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
    }

    protected function databaseSerializeNotNull($primitive_value)
    {
        return json_encode($primitive_value);
    }

    protected function toPrimitiveNotNull($cast_value)
    {
        return array_map(function ($value) {
            return $this->sub_field->toPrimitive($value);
        }, $cast_value);
    }



    public function getValidationRules()
    {
        $rules = parent::getValidationRules();
        $rules[] = 'array';
        return $rules;
    }

    public function validate(array $data, string $path, MessageBag $messages)
    {
        parent::validate($data, $path, $messages);
        $my_data = data_get($data, $path);
        if (is_array($my_data)) {
            foreach (array_keys($my_data) as $i) {
                $this->sub_field->validate($data, "{$path}.{$i}", $messages);
            }
        }
    }
}
