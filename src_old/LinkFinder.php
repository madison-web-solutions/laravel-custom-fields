<?php
namespace MadisonSolutions\LCF;

abstract class LinkFinder
{
    abstract public function getSuggestions(string $search) : array;
    abstract public function lookup(string $spec) : ?array;
    abstract public function getUrl(string $spec) : ?string;
    abstract public function getLabel(string $spec) : ?string;
    abstract public function getDisplayName(string $spec) : ?string;
}
