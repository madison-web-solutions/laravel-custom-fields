<?php

namespace MadisonSolutions\LCF;

class AuthPolicy
{
    public function getSuggestions($user = null)
    {
        return true;
    }

    public function getDisplayName($user = null)
    {
        return true;
    }

    public function linkLookup($user = null)
    {
        return true;
    }

    public function markdown($user = null)
    {
        return true;
    }
}
