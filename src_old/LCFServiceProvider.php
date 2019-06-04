<?php
namespace MadisonSolutions\LCF;

use Illuminate\Support\ServiceProvider;

class LCFServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(LCF::class, function ($app) {
            return new LCF();
        });
    }

    public function boot()
    {
        $this->loadRoutesFrom(dirname(__DIR__) . '/routes.php');
        $this->loadMigrationsFrom(dirname(__DIR__) . '/migrations');
    }
}
