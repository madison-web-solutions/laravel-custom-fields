<?php

namespace MadisonSolutions\LCF;

class LinkFinder
{
    public function getSuggestions(string $search) : array
    {
        $out = [];
        // search pages for $search and for each matching page,
        // append an array to $out with 'id' and 'label' keys
        return $out;
    }

    public function lookup(string $link_id) : ?array
    {
        // lookup page with id = $id, if not found, return null
        // otherwise return array with keys:
        //   'url' - the fully qualified url of the page
        //   'label' - the label that would be used by default for the link (eg the page name)
        //   'display_name' - what to show in the form field
        return null;
    }

    public function getUrl(string $link_id) : ?string
    {
        $details = $this->lookup($link_id);
        return $details ? $details['url'] : null;
    }

    public function getLabel(string $link_id) : ?string
    {
        $details = $this->lookup($link_id);
        return $details ? $details['label'] : null;
    }

    public function getDisplayName(string $link_id) : ?string
    {
        $details = $this->lookup($link_id);
        return $details ? $details['display_name'] : null;
    }
}
