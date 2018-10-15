<?php
namespace MadisonSolutions\LCF;

trait FieldAttributesTrait
{
    public function setAttribute($key, $value)
    {
        $field = $this->getField($key);
        if ($field) {
            $this->attributes[$key] = $field->toDatabase($value);
            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
        $field = $this->getField($key);
        if ($field) {
            $db_value = $this->getAttributeFromArray($key);
            return $field->fromDatabase($db_value) ?? $field->defaultValue();
        }

        return parent::getAttribute($key);
    }

    public function getField($key)
    {
        return resolve(LCF::class)->getFieldFor($this, $key);
    }
}
