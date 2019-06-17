<?php

namespace MadisonSolutions\LCF;

class LinkFinder
{
    public function getSuggestions(string $search, int $page = 1) : SearchResultSet
    {
        $results = new SearchResultSet(false);
        // search pages for $search and for each matching page,
        // append a LinkSearchResult to $results
        return $out;
    }

    public function lookup(string $link_id) : ?LinkSearchResult
    {
        // lookup page with id = $link_id, if not found, return null
        // otherwise return a LinkSearchResult
        return null;
    }
}
