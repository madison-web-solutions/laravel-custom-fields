<?php

namespace MadisonSolutions\LCF;

use Request;
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
        Request::macro('lcfCoerce', function (array $fields) {
            foreach ($fields as $field_name => $field) {
                $field->coerce($this->input($field_name), $output, true);
                $this->offsetSet($field_name, $output);
            }
        });

        Request::macro('lcfValidate', function (array $fields, array $extra_rules = []) {
            $v = new Validator($this->all(), $fields, $extra_rules);
            $v->validate();
        });

        //$this->loadRoutesFrom(dirname(__DIR__) . '/routes.php');
        //$this->loadMigrationsFrom(dirname(__DIR__) . '/migrations');
    }
}
