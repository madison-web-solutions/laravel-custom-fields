<?php

namespace MadisonSolutions\LCF;

use Gate;
use Request;
use Route;
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
        // publish the lcf config file
        $this->publishes([
            dirname(__DIR__).'config.example.php' => config_path('lcf.php'),
        ]);

        // Define the policies for authorizing ajax actions
        Gate::policy(LCF::class, config('lcf.auth_policy_class', AuthPolicy::class));
        Gate::policy(Media\MediaItem::class, config('lcf.media_auth_policy_class', Media\AuthPolicy::class));

        // Add convenience method to Request object for coercing data
        Request::macro('lcfCoerce', function (array $fields) {
            foreach ($fields as $field_name => $field) {
                $field->coerce($this->input($field_name), $output, true);
                $this->offsetSet($field_name, $output);
            }
        });

        // Add convenience method to Request object for validating field data
        Request::macro('lcfValidate', function (array $fields, array $extra_rules = []) {
            $v = new Validator($this->all(), $fields, $extra_rules);
            $v->validate();
        });

        // Define the routes
        if (! $this->app->routesAreCached()) {
            Route::namespace('MadisonSolutions\LCF')
                ->prefix('lcf')
                ->middleware(config('route_middleware', ['web']))
                ->group(function () {
                    Route::get('suggestions', 'Controller@getSuggestions');
                    Route::get('display-name', 'Controller@getDisplayName');
                    Route::get('link-lookup', 'Controller@linkLookup');
                    Route::post('markdown', 'Controller@markdown');

                    Route::get('media-library', 'Media\Controller@index');
                    Route::post('media-library', 'Media\Controller@upload');
                    Route::get('media-library/{id}', 'Media\Controller@get');
                    Route::post('media-library/{id}', 'Media\Controller@update');
                    Route::post('media-library/{id}/delete', 'Media\Controller@delete');
                });
        }

        //$this->loadMigrationsFrom(dirname(__DIR__) . '/migrations');
    }
}
