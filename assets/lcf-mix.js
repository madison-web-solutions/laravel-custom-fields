const mix = require('laravel-mix');

mix.extend('lcf', function(webpackConfig) {
    // Tell webpack where to find the 'lcf' modules
    //console.log(webpackConfig);
    webpackConfig.resolve.alias.lcf = __dirname;

    const CKEStyles = require('@ckeditor/ckeditor5-dev-utils').styles;
    const CKERegex = {
        svg: /ckeditor5-[^/\\]+[/\\]theme[/\\]icons[/\\][^/\\]+\.svg$/,
        css: /ckeditor5-[^/\\]+[/\\]theme[/\\].+\.css/,
    };

    // exclude CKE regex from mix's default rules
    const targetSVG = /(\.(png|jpe?g|gif|webp)$|^((?!font).)*\.svg$)/;
    const targetCSS = /\.css$/;
    for (let rule of webpackConfig.module.rules) {
        if (rule.test.toString() === targetSVG.toString()) {
            rule.exclude = CKERegex.svg;
        }
        else if (rule.test.toString() === targetCSS.toString()) {
            rule.exclude = CKERegex.css;
        }
    }

    // Add rules for CKE svg and css files
    webpackConfig.module.rules.unshift({
        test: CKERegex.svg,
        use: ['raw-loader']
    });
    webpackConfig.module.rules.unshift({
        test: CKERegex.css,
        use: [
            {
                loader: 'style-loader',
                options: {
                    singleton: true
                }
            },
            {
                loader: 'postcss-loader',
                options: CKEStyles.getPostCssConfig({
                    themeImporter: {
                        themePath: require.resolve('@ckeditor/ckeditor5-theme-lark')
                    },
                    minify: true
                })
            }
        ]
    });
});
