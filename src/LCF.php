<?php

namespace MadisonSolutions\LCF;

use Route;

class LCF
{
    protected $markdown_instance;
    protected $link_finder_instance;

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
        'ChoiceField' => Fields\ChoiceField::class,
        'CompoundField' => Fields\CompoundField::class,
        'CurrencyField' => Fields\CurrencyField::class,
        'DateField' => Fields\DateField::class,
        'IntegerField' => Fields\IntegerField::class,
        'LinkField' => Fields\LinkField::class,
        'MarkdownField' => Fields\MarkdownField::class,
        'MediaIdField' => Fields\MediaIdField::class,
        'ModelIdField' => Fields\ModelIdField::class,
        'NumberField' => Fields\NumberField::class,
        'OptionsField' => Fields\OptionsField::class,
        'QuantityWithUnitField' => Fields\QuantityWithUnitField::class,
        'RepeaterField' => Fields\RepeaterField::class,
        'SwitchField' => Fields\SwitchField::class,
        'TextAreaField' => Fields\TextAreaField::class,
        'TextField' => Fields\TextField::class,
        'TimeField' => Fields\TimeField::class,
        'TimestampField' => Fields\TimestampField::class,
        'ToggleField' => Fields\ToggleField::class,
    ];

    public static function make(string $classname, array $options)
    {
        $classname = self::$classmap[$classname] ?? $classname;
        if (! is_a($classname, Field::class, true)) {
            throw new \Exception("No LCF Field '{$classname}'");
        }
        return new $classname($options);
    }

    public static function __callStatic($method, $args)
    {
        if (preg_match('/^new(\w+)Field$/', $method, $matches)) {
            return self::make($matches[1].'Field', $args[0]);
        }
        throw new \Exception("Unrecognised static method '{$name}'");
    }

    public static function iLikeOperator(\Illuminate\Database\ConnectionInterface $connection)
    {
        // Sometimes we need to search the database in a case-insensitive way
        // The Postgres LIKE operator is case-sensitive by default but they support a non-standard operator ILIKE instead
        // The MySQL LIKE operator is case-insensitive anyway by default
        return (($connection instanceof Illuminate\Database\PostgresConnection) ? 'ILIKE' : 'LIKE');
    }
}
