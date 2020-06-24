const mix = require('laravel-mix');

mix.js('resources/assets/js/app.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .copy('node_modules/bootstrap-sass/assets/fonts/bootstrap/','public/fonts/bootstrap')
    .sass('resources/assets/sass/vendor.scss', './public/css/vendor.css')
    .extract(['bootstrap-sass']);


mix.webpackConfig({
    devServer: {
        proxy: {
            '*': 'http://localhost:80'
        }
    }
});

// version does not work in hmr mode
if (process.env.npm_lifecycle_event !== 'hot') {
    mix.version()
}
