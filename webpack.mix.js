const mix = require('laravel-mix');

mix
    .setPublicPath('public')
    .js('resources/js/app.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css', [
        require('tailwindcss'),
        require('autoprefixer'),
    ])
    .version()
    .options({
        processCssUrls: false
    });

// Set the public path for assets
mix.setResourceRoot('/ERP-SYSTEM/');

// Override the mix public path
if (!mix.inProduction()) {
    mix.options({
        hmrOptions: {
            host: '64.23.150.233',
            port: 8080
        }
    });
}
