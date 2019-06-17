<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\LCF\LCF;

class MarkdownField extends TextAreaField
{
    public function inputComponent() : string
    {
        return 'lcf-markdown-input';
    }

    protected function doExpandNotNull($cast_value)
    {
        return app(LCF::class)->getMarkdown()->text($cast_value);
    }
}
