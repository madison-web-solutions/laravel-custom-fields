<?php
namespace MadisonSolutions\LCF\Fields;

class MarkdownField extends TextAreaField
{
    public function inputComponent() : string
    {
        return 'lcf-markdown-input';
    }
}
