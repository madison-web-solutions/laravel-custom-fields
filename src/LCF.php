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
}
