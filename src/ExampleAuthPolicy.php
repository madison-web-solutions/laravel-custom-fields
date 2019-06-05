<?php

namespace MadisonSolutions\LCF;

use Log;

class ExampleAuthPolicy
{
    public function getSuggestions($user)
    {
        \Log::warning("Access to LCF.getSuggestions denied by ExampleAuthPolicy - You probably need to define a Policy for LCF routes.");
        return false;
    }

    public function getDisplayName($user)
    {
        \Log::warning("Access to LCF.getDisplayName denied by ExampleAuthPolicy - You probably need to define a Policy for LCF routes.");
        return false;
    }

    public function linkLookup($user)
    {
        \Log::warning("Access to LCF.linkLookup denied by ExampleAuthPolicy - You probably need to define a Policy for LCF routes.");
        return false;
    }

    public function markdown($user)
    {
        \Log::warning("Access to LCF.markdown denied by ExampleAuthPolicy - You probably need to define a Policy for LCF routes.");
        return false;
    }
}
