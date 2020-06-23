const elixir = require('laravel-elixir');
const path = require('path');

require('laravel-elixir-webpack-official');
require('laravel-elixir-vue-2');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */
elixir(mix => {
    // Elixir.webpack.config.module.loaders = [];
    Elixir.webpack.mergeConfig({
        resolveLoader: {
            root: path.join(__dirname, 'node_modules'),
        },
        module: {
            loaders: [
                {
                    test: /\.css$/,
                    loader: 'style!css'
                }
            ]
        }
    });


    mix.sass('app.scss')
        .version('public/css/app.css')
        .webpack('app.js')
        .version('public/js/app.js')
        .copy('node_modules/bootstrap-sass/assets/fonts/bootstrap/','public/fonts/bootstrap')
    //.browserSync({proxy : 'localhost:1337/Flarepoint-crm/public/tasks'});
});