<?php

namespace MadisonSolutions\LCF;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use MadisonSolutions\LCF\FindableModelInterface;

class FindableModelRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return is_string($value) && is_a($value, Model::class, true) && is_a($value, FindableModelInterface::class, true);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a subclass of ' . Model::class . ' and must implement ' . FindableModelInterface::class;
    }
}
