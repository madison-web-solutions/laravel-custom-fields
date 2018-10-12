<?php
namespace MadisonSolutions\LCF;

use MadisonSolutions\LCF\Group\FieldGroup;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\MessageBag;
use Validator;
use SplObjectStorage;

class LCF
{
    protected $obj_fields_cache;
    protected $field_groups;

    public function __construct()
    {
        $this->obj_fields_cache = new SplObjectStorage();
        $this->field_groups = new SplObjectStorage();
    }

    public function registerFieldGroup(FieldGroup $group)
    {
        $this->field_groups->attach($group);
    }

    /**
     * Return the field object defined for the given object with the given name
     * Returns false if no field is defined on the object with that name
     */
    public function getField(object $obj, string $field_name) : ?Field
    {
        $fields = $this->getFieldsFor($obj);
        return $fields[$field_name] ?? null;
    }

    public function getFieldGroupsFor(object $obj)
    {
        $groups = [];
        foreach ($this->field_groups as $group) {
            if ($group->appliesTo($obj)) {
                $groups[] = $group;
            }
        }
        return $groups;
    }

    public function getFieldsFor(object $obj)
    {
        if (! $this->obj_fields_cache->contains($obj)) {
            $fields = [];
            foreach ($this->getFieldGroupsFor($obj) as $group) {
                foreach ($group as $key => $field) {
                    $fields[$key] = $field;
                }
            }
            $this->obj_fields_cache[$obj] = $fields;
        }
        return $this->obj_fields_cache[$obj];
    }

    public function fromDatabase(object $obj, string $field_name, string $db_value)
    {
        $field = $this->getField($obj, $field_name);
        return $field ? $field->fromDatabase($db_value) : null;
    }

    public function toDatabase(object $obj, string $field_name, $value) : string
    {
        $field = $this->getField($obj, $field_name);
        return $field ? $field->toDatabase($value) : null;
    }

    protected function mergeGroupFields(FieldGroup $group, array &$fields)
    {
        foreach ($group as $key => $field) {
            $fields[$key] = $field;
        }
    }

    public function validate(array $data, array $groups, array $extra_rules = [])
    {
        $fields = [];
        foreach ($groups as $group) {
            $this->mergeGroupFields($group, $fields);
        }
        return $this->validateFields($data, $fields, $extra_rules);
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
}
