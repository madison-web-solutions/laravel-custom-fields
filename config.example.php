<?php

return [
    'route_middleware' => ['web'],
    'auth_policy_class' => \MadisonSolutions\LCF\AuthPolicy::class,
    'markdown_class' => \MadisonSolutions\LCF\Markdown::class,
    'link_finder_class' => \MadisonSolutions\LCF\LinkFinder::class,
    'model_finder_class' => \MadisonSolutions\LCF\ModelFinder::class,
    'automatically_create_webp_images' => true,
    'media_auth_policy_class' => \MadisonSolutions\LCF\Media\AuthPolicy::class,
    'media_disk_name' => 'public',
    'media_dir_name' => 'lcf_media',
];
