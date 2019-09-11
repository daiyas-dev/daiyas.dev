const mix = require('laravel-mix');
require('laravel-mix-alias');

mix
    .alias('@', 'resources/js')
    .alias('@vc', 'resources/js/components')
    .alias('@vcs', 'resources/js/components/Shared')
    .alias('@vcp', 'resources/js/components/Pages')
    .alias('~~', 'node_modules')
    .js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/daiyas.scss', 'public/css')
    .extract([
        'bootstrap',
        'popper.js',
        'vue',
        'vue-router',
        'vue-template-compiler',
        'vue-bootstrap-datetimepicker',
        'lodash',
        'axios',
        'jquery',
        'jquery-ui-pack',
        'jquery-contextmenu',
        '@fortawesome/fontawesome-free',
        'pqgrid',
        'jszip',
        'moment',
        'decimal.js',
        'algebra.js',
        'html2canvas',
        'jspdf',
        'deep-object-diff',
        'laravel-echo',
        'socket.io-client'
    ])
    // .version()
    // .sourceMaps()
    .disableNotifications()
    .webpackConfig({
        devServer: {
            overlay: true,
        },
    })
    .options({
        hmrOptions: {
            host: 'daiyas.dev',
            port: '8080'
        }
    })
    ;
