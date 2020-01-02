## Installation

Create an LCF config file in your app's config directory
`php artisan vendor:publish`

Edit as necessary.
If your config is cached, remember to run `php artisan config:cache` after editing, to save the changes to the cache.

Create the LCF media_items database table
`php artisan migrate`

Fetch the required NPM libraries.
You should already have `axios`, `laravel-mix`, `vue` and `vue-template-compiler` from the default Laravel installation.
You'll also need the following:
```
"@ckeditor/ckeditor5-basic-styles": "^16.0.0",
"@ckeditor/ckeditor5-block-quote": "^16.0.0",
"@ckeditor/ckeditor5-dev-utils": "^12.0.5",
"@ckeditor/ckeditor5-editor-classic": "^16.0.0",
"@ckeditor/ckeditor5-essentials": "^16.0.0",
"@ckeditor/ckeditor5-heading": "^16.0.0",
"@ckeditor/ckeditor5-horizontal-line": "^16.0.0",
"@ckeditor/ckeditor5-link": "^16.0.0",
"@ckeditor/ckeditor5-list": "^16.0.0",
"@ckeditor/ckeditor5-paragraph": "^16.0.0",
"@ckeditor/ckeditor5-paste-from-office": "^16.0.0",
"@ckeditor/ckeditor5-table": "^16.0.0",
"@ckeditor/ckeditor5-theme-lark": "^16.0.0",
"lodash-es": "^4.17.11",
```

Edit your webpack.mix config to include the LCF extension
This lets webpack know where to find the lcf javascript and css files, and adds the required setup for CKEditor
```
const mix = require('laravel-mix');
require('./laravel-custom-fields/assets/lcf-mix.js');
```

Then add the LCF extension into your mix pipeline, for example:
```
mix
    .lcf()
    .js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');
```

Include the LCF module in your app.js and call the init function
```
import LCF from 'lcf';
LCF.init();
```

Import the LCF styles into your app.scss (you can override LCF sass variables prior to this)
```
@import '~lcf/lcf.scss';
```
