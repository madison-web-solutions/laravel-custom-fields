## Installation

Create an LCF config file in your app's config directory
`php artisan vendor:publish`

Edit as necessary.
If your config is cached, remember to run `php artisan config:cache` after editing, to save the changes to the cache.

Create the LCF media_items database table
`php artisan migrate`

Edit your webpack config file so that it knows where to find the LCF module to include the LCF js and css
```
mix.extend('resolveLcf', function(webpackConfig) {
    webpackConfig.resolve.alias.lcf$ = path.resolve(__dirname, 'vendor/madison-solutions/laravel-custom-fields/assets/lcf.js');
});

mix.resolveLcf()
    .js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .sass('laravel-custom-fields/assets/lcf.scss', 'public/css');
```

Include the LCF module in your app.js and call the init function
```
import LCF from 'lcf';
LCF.init();
```
