<?php

namespace MadisonSolutions\LCF\Media;

class AuthPolicy
{
    public function index($user = null)
    {
        return true;
    }

    public function get($user = null, $item = null)
    {
        return true;
    }

    public function upload($user = null)
    {
        return true;
    }

    public function update($user = null, $item = null)
    {
        return true;
    }

    public function delete($user = null, $item = null)
    {
        return true;
    }
}
