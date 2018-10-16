<?php

namespace MadisonSolutions\LCF;

use Parsedown;

class Markdown extends Parsedown
{
    public function __construct()
    {
        $this->setBreaksEnabled(true);
        $this->setMarkupEscaped(true);

        $this->InlineTypes['='][] = 'Highlight';
        $this->inlineMarkerList .= '=';
    }

    protected function inlineHighlight($excerpt)
    {
        if (! isset($excerpt['text'][1])) {
            return;
        }

        if ($excerpt['text'][1] === '=' and preg_match('/^==(?=\S)(.+?)(?<=\S)==/', $excerpt['text'], $matches)) {
            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'mark',
                    'text' => $matches[1],
                    'handler' => 'line',
                ],
            ];
        }
    }
}
