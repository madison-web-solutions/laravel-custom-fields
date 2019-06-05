<?php

return [
    'route_middleware' => ['web'],
    'auth_policy_class' => \MadisonSolutions\LCF\AuthPolicy::class,
    'media_auth_policy_class' => \MadisonSolutions\LCF\Media\AuthPolicy::class,
    'markdown_class' => \MadisonSolutions\LCF\Markdown::class,
    'link_finder_class' => \MadisonSolutions\LCF\LinkFinder::class,
];
