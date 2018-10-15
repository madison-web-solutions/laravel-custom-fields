<?php
namespace MadisonSolutions\LCF\Group;

use MadisonSolutions\LCF\LCF;
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

    public function getField($path)
    {
        $field_key = LCF::shiftPath($path);
        $field = $this->fields[$field_key] ?? null;
        while ($sub_key = LCF::shiftPath($path) && $field) {
            $field = $field->getSubField($sub_key);
        }
        return $field;
    }

    public function appliesTo(object $obj)
    {
        return $this->test->appliesTo($obj);
    }

    public function coerce(array $data, int $on_fail = Field::COERCE_FAIL_THROW)
    {
        $values = [];
        foreach ($this->fields as $key => $field) {
            $values[$key] = $field->coerce($data[$key] ?? null, $on_fail);
        }
        return $values;
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
