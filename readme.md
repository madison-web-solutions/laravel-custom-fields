## Installation

Create an LCF config file in your app's config directory
`php artisan vendor:publish`

Edit as necessary.
If your config is cached, remember to run `php artisan config:cache` after editing, to save the changes to the cache.

Create the LCF media_items database table
`php artisan migrate`

Edit your webpack config file to include the LCF js and css
```
mix.js('resources/js/app.js', 'public/js')
    .js('laravel-custom-fields/assets/lcf.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .sass('laravel-custom-fields/assets/lcf.scss', 'public/css');
```
