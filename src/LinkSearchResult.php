<?php

namespace MadisonSolutions\LCF;

class LinkSearchResult extends SearchResult
{
    protected $url;
    protected $label;

   //   'id' - the unique 'linkid' for this object
   //   'display_name' - what to show in the form field
   //   'url' - the fully qualified url of the page
   //   'label' - the label that would be used by default for the link (eg the page name)
    public function __construct(string $id, string $display_name, string $url, string $label)
    {
        parent::__construct($id, $display_name);
        $this->url = $url;
        $this->label = $label;
    }

    public function __get($key)
    {
        switch ($key) {
            case 'url':
            case 'label':
                return $this->$key;
        }
        return parent::__get($key);
    }

    public function __isset($key)
    {
        switch ($key) {
            case 'url':
            case 'label':
                return true;
        }
        return parent::__isset($key);
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        $data['url'] = $this->url;
        $data['label'] = $this->label;
        return $data;
    }
}
