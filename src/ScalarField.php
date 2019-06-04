<?php

namespace MadisonSolutions\LCF;

abstract class ScalarField extends Field
{
    public function fieldComponent() : string
    {
        return 'lcf-scalar-field';
    }

    public function inputComponent() : ?string
    {
        return 'lcf-text-input';
    }
}
