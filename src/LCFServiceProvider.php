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
        $lcf_root_dir = dirname(__DIR__);

        // Publish the lcf config file
        $this->publishes([
            "{$lcf_root_dir}/config.example.php" => config_path('lcf.php'),
        ]);

        // Load translations
        $this->loadTranslationsFrom("{$lcf_root_dir}/lang", 'lcf');

        // Load migrations
        $this->loadMigrationsFrom("{$lcf_root_dir}/migrations");

        // Define the policies for authorizing ajax actions
        Gate::policy(LCF::class, config('lcf.auth_policy_class', ExampleAuthPolicy::class));
        Gate::policy(Media\MediaItem::class, config('lcf.media_auth_policy_class', Media\ExampleAuthPolicy::class));

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
                    Route::get('model-suggestions', 'Controller@getModelSuggestions');
                    Route::get('link-suggestions', 'Controller@getLinkSuggestions');
                    Route::get('model-lookup', 'Controller@lookupModel');
                    Route::get('link-lookup', 'Controller@lookupLink');
                    Route::post('markdown', 'Controller@markdown');

                    Route::get('media-library', 'Media\Controller@index');
                    Route::get('media-library/folders', 'Media\Controller@folders');
                    Route::get('media-library/folders/new', 'Media\Controller@createFolder');
                    Route::post('media-library/folders/new', 'Media\Controller@createFolder');
                    Route::get('media-library/folders/{id}', 'Media\Controller@editFolder');
                    Route::post('media-library/folders/{id}', 'Media\Controller@editFolder');
                    Route::post('media-library/folders/{id}/delete', 'Media\Controller@deleteFolder');
                    Route::post('media-library', 'Media\Controller@upload');
                    Route::get('media-library/{id}', 'Media\Controller@get');
                    Route::post('media-library/{id}', 'Media\Controller@update');
                    Route::post('media-library/{id}/delete', 'Media\Controller@delete');
                });
        }
    }
}
