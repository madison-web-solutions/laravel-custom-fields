<?php

namespace MadisonSolutions\LCF\Media;

use Log;

class ExampleAuthPolicy
{
    public function index($user)
    {
        \Log::warning("Access to LCF.Media.index denied by ExampleAuthPolicy - You probably need to define a Policy for LCF Media routes.");
        return false;
    }

    public function upload($user)
    {
        \Log::warning("Access to LCF.Media.upload denied by ExampleAuthPolicy - You probably need to define a Policy for LCF Media routes.");
        return false;
    }

    public function manageFolders($user)
    {
        \Log::warning("Access to LCF.Media.manageFolders denied by ExampleAuthPolicy - You probably need to define a Policy for LCF Media routes.");
        return false;
    }

    public function get($user, MediaItem $item)
    {
        \Log::warning("Access to LCF.Media.get denied by ExampleAuthPolicy - You probably need to define a Policy for LCF Media routes.");
        return false;
    }

    public function update($user, MediaItem $item)
    {
        \Log::warning("Access to LCF.Media.update denied by ExampleAuthPolicy - You probably need to define a Policy for LCF Media routes.");
        return false;
    }

    public function delete($user, MediaItem $item)
    {
        \Log::warning("Access to LCF.Media.delete denied by ExampleAuthPolicy - You probably need to define a Policy for LCF Media routes.");
        return false;
    }
}
