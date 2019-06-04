<?php
namespace MadisonSolutions\LCF;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\MessageBag;
use Validator;

class LCF
{
    protected $markdown_instance;
    protected $link_finder_instance;

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

    public function getMarkdown()
    {
        if (is_null($this->markdown_instance)) {
            $markdown_class = config('lcf.markdown_class', Markdown::class);
            $this->markdown_instance = new $markdown_class();
        }
        return $this->markdown_instance;
    }

    public function getLinkFinder()
    {
        if (is_null($this->link_finder_instance)) {
            $link_finder_class = config('lcf.link_finder_class');
            if (! is_a($link_finder_class, LinkFinder::class, true)) {
                throw new \Exception('config lcf.link_finder_class does not define a class extending from ' . LinkFinder::class);
            }
            $this->link_finder_instance = new $link_finder_class();
        }
        return $this->link_finder_instance;
    }

    protected static $classmap = [
        'ChoiceField' => ChoiceField::class,
        'CompoundField' => CompoundField::class,
        'IntegerField' => IntegerField::class,
        'LinkField' => LinkField::class,
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
