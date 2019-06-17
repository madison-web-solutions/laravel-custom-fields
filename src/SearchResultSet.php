<?php

namespace MadisonSolutions\LCF;

use JsonSerializable;

class SearchResultSet implements JsonSerializable
{
    protected $has_more;
    protected $results;

    public function __construct(bool $has_more = false)
    {
        $this->has_more = $has_more;
        $this->results = [];
    }

    public function addResult(SearchResult $result)
    {
        $this->results[] = $result;
    }

    public function jsonSerialize()
    {
        return [
            'has_more' => $this->has_more,
            'results' => $this->results,
        ];
    }
}
