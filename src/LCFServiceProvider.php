<?php
namespace MadisonSolutions\LCF;

use Illuminate\Support\ServiceProvider;

class LCFServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(LCF::class, function ($app) {
            $lcf = new LCF();
            $field_groups_file = app_path() . '/lcf-field-groups.php';
            if (file_exists($field_groups_file)) {
                include($field_groups_file);
            }
            return $lcf;
        });
    }

    public function boot()
    {
        $this->loadRoutesFrom(dirname(__DIR__) . '/routes.php');
        $this->loadMigrationsFrom(dirname(__DIR__) . '/migrations');
    }
}
