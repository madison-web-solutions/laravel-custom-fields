<?php

namespace MadisonSolutions\LCF;

interface FindableModelInterface
{
    public function lcfGetSuggestions(string $search, int $page = 1, ?string $context = null) : SearchResultSet;

    public function lcfLookup($id, ?string $context = null) : ?SearchResult;
}
