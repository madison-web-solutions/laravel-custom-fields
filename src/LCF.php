<?php

namespace MadisonSolutions\LCF;

use Illuminate\Validation\ValidationException;
use Validator;

class LCF
{
    //protected $markdown_instance;
    //protected $link_finder_instance;

    public static function shiftPath(&$path)
    {
        if (is_string($path)) {
            $path = empty($path) ? [] : explode('.', $path);
        }
        return array_shift($path);
    }

    public function validate(array $data, array $rules)
    {
        Validator::make($data, $rules)->validate();
    }

    /*
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
    */

    protected static $classmap = [
        'ChoiceField' => Fields\ChoiceField::class,
        'CompoundField' => Fields\CompoundField::class,
        'IntegerField' => Fields\IntegerField::class,
        'LinkField' => Fields\LinkField::class,
        'MarkdownField' => Fields\MarkdownField::class,
        'MediaField' => Fields\MediaField::class,
        'ModelField' => Fields\ModelField::class,
        'OptionsField' => Fields\OptionsField::class,
        'QuantityWithUnitField' => Fields\QuantityWithUnitField::class,
        'RepeaterField' => Fields\RepeaterField::class,
        'SwitchField' => Fields\SwitchField::class,
        'TextAreaField' => Fields\TextAreaField::class,
        'TextField' => Fields\TextField::class,
        'TimestampField' => Fields\TimestampField::class,
        'ToggleField' => Fields\ToggleField::class,
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
