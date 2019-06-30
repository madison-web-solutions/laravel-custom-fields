<?php

namespace MadisonSolutions\LCF;

use Log;

class ExampleAuthPolicy
{
    public function lookupModels($user, string $model_class, ?string $finder_context)
    {
        \Log::warning("Access to LCF.lookupModels denied by ExampleAuthPolicy - You probably need to define a Policy for LCF routes.");
        return false;
    }

    public function lookupLinks($user)
    {
        \Log::warning("Access to LCF.lookupLinks denied by ExampleAuthPolicy - You probably need to define a Policy for LCF routes.");
        return false;
    }

    public function markdown($user)
    {
        \Log::warning("Access to LCF.markdown denied by ExampleAuthPolicy - You probably need to define a Policy for LCF routes.");
        return false;
    }
}
