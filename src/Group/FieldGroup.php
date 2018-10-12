<?php
namespace MadisonSolutions\LCF\Group;

use MadisonSolutions\LCF\Field;
use JsonSerializable;
use Iterator;

class FieldGroup implements JsonSerializable, Iterator
{
    protected $test;
    protected $fields;

    public function jsonSerialize()
    {
        return [
            'test' => $this->test,
            'fields' => $this->fields,
        ];
    }

    public function __construct(ObjectTest $test)
    {
        $this->test = $test;
        $this->fields = [];
    }

    public function addField(string $key, Field $field)
    {
        $this->fields[$key] = $field;
    }

    public function addValidationRules(array &$rules)
    {
        foreach ($this->fields as $key => $field) {
            $field->addValidationRules($rules, $key);
        }
    }

    public function fromPrimitives(array $primitive_values)
    {
        $values = [];
        foreach ($this->fields as $key => $field) {
            $values[$key] = $field->fromPrimitive($primitive_values[$key] ?? null);
        }
        return $values;
    }

    public function appliesTo(object $obj)
    {
        return $this->test->appliesTo($obj);
    }

    public function current()
    {
        return current($this->fields);
    }

    public function key()
    {
        return key($this->fields);
    }

    public function next()
    {
        next($this->fields);
    }

    public function rewind()
    {
        reset($this->fields);
    }

    public function valid()
    {
        return ! is_null(key($this->fields));
    }
}
