<?php
namespace MadisonSolutions\LCF;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\MessageBag;
use Validator;

class LCF
{
    public static function shiftPath(&$path)
    {
        if (is_string($path)) {
            $path = empty($path) ? [] : explode('.', $path);
        }
        return array_shift($path);
    }

    public function validateFields(array $data, array $fields, array $extra_rules = [])
    {
        $validator = Validator::make($data, $extra_rules);
        $messages = $validator->messages();
        foreach ($fields as $key => $field) {
            $this->validateField($data, $key, $field, $messages);
        }
        if (! $messages->isEmpty()) {
            throw new ValidationException($validator);
        }
    }

    protected function validateField(array $data, string $key, Field $field, MessageBag $messages)
    {
        $field->validate($data, $key, $messages);
    }

    protected static $classmap = [
        'ChoiceField' => ChoiceField::class,
        'CompoundField' => CompoundField::class,
        'IntegerField' => IntegerField::class,
        'MarkdownField' => MarkdownField::class,
        'MediaField' => MediaField::class,
        'ModelField' => ModelField::class,
        'OptionsField' => OptionsField::class,
        'QuantityWithUnitField' => QuantityWithUnitField::class,
        'RepeaterField' => RepeaterField::class,
        'SwitchField' => SwitchField::class,
        'TextAreaField' => TextAreaField::class,
        'TextField' => TextField::class,
        'TimestampField' => TimestampField::class,
        'ToggleField' => ToggleField::class,
    ];

    public static function make(string $classname, array $options)
    {
        if (! is_a($classname, Field::class, true)) {
            throw new \Exception("No LCF Field '{$classname}'");
        }
        return new $classname($options);
    }

    public static function __callStatic($method, $args)
    {
        foreach (self::$classmap as $alias => $classname) {
            if ($method === "new{$alias}") {
                return self::make($classname, $args[0]);
            }
        }
        throw new \Exception("Unrecognised static method '{$name}'");
    }
}
