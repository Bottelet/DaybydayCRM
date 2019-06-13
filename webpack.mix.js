const { mix } = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
  .sass('resources/sass/app.scss', 'public/css')
  .copy('node_modules/bootstrap-sass/assets/fonts/bootstrap/','public/fonts/bootstrap');
