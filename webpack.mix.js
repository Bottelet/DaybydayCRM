const mix = require('laravel-mix');
const webpack = require('webpack');

mix.js('resources/assets/js/app.js', 'public/js').vue()
    .js('resources/assets/js/jquery-init.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .copy('node_modules/bootstrap-sass/assets/fonts/bootstrap/','public/fonts/bootstrap')
    .sass('resources/assets/sass/vendor.scss', './public/css/vendor.css')
    .extract(['bootstrap-sass', 'jquery'])
    .copy('node_modules/bootstrap-select/dist/css/bootstrap-select.min.css', 'public/css/bootstrap-select.min.css');


mix.webpackConfig({
    devServer: {
        proxy: {
            '*': 'http://localhost:80'
        }
    },
    plugins: [
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery',
            'window.jQuery': 'jquery',
        })
    ]
});

// version does not work in hmr mode
if (process.env.npm_lifecycle_event !== 'hot') {
    mix.version()
}
