<?php
namespace MadisonSolutions\LCF;

trait FieldAttributesTrait
{
    protected static $fields_cache;

    public function getField($key, $use_cache = true)
    {
        $class = get_class($this);
        if ($use_cache) {
            if (! isset(self::$fields_cache[$class])) {
                self::$fields_cache[$class] = [];
            }
            if (isset(self::$fields_cache[$class][$key])) {
                return self::$fields_cache[$class][$key];
            }
        }
        $method = 'get' . studly_case($key) . 'Field';
        if (method_exists($this, $method)) {
            $field = $this->$method();
            if (!($field instanceof Field)) {
                throw new \Exception("{$class}::{$method} did not return a Field");
            }
        } else {
            throw new \Exception("{$class}::{$method} does not exist");
            $field = false;
        }
        if ($use_cache) {
            self::$fields_cache[$class][$key] = $field;
        }
        return $field;
    }

    public function hasLcfField($key)
    {
        return method_exists($this, 'get'.studly_case($key).'Field');
    }

    public function hasGetMutator($key)
    {
        return parent::hasGetMutator($key) || $this->hasLcfField($key);
    }

    protected function getLcfFieldValue($key, $value)
    {
        $field = $this->getField($key);
        return $field->fromDatabase($value) ?? $field->defaultValue();
    }

    protected function setLcfFieldValue($key, $value)
    {
        $field = $this->getField($key);
        $this->attributes[$key] = $field->toDatabase($value);
    }

    protected function mutateAttribute($key, $value)
    {
        if (parent::hasGetMutator($key)) {
            return parent::mutateAttribute($key, $value);
        } else {
            return $this->getLcfFieldValue($key, $value);
        }
    }

    public function hasSetMutator($key)
    {
        return parent::hasSetMutator($key) || $this->hasLcfField($key);
    }

    protected function setMutatedAttributeValue($key, $value)
    {
        if (parent::hasSetMutator($key)) {
            return parent::setMutatedAttributeValue($key, $value);
        } else {
            return $this->setLcfFieldValue($key, $value);
        }
    }


    /* old
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
    */
}
