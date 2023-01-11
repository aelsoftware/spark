const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js/app.js').vue()
    .postCss('resources/css/spark.css', 'public/css/app.css', [
        require('postcss-import'),
        require('tailwindcss'),
    ]);

mix.disableSuccessNotifications();
