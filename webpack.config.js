var Encore = require('@symfony/webpack-encore');
Encore
    .setOutputPath('public/build/') // Указываем директории где будут храниться скомпилированные стили
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild() // Очищаем все от лишнего до компиляции
    .enableSourceMaps(!Encore.isProduction()) // Указываем что мы НЕ на продакшене
    .addEntry('js/app', [
        './assets/js/app.js',
        './node_modules/bootstrap/dist/js/bootstrap.min.js'
    ]) // Указываем где хранятся JS
    .addStyleEntry('css/app', [
        './assets/css/app.css',
        './node_modules/bootstrap/dist/css/bootstrap.min.css'
    ]); // Указываем где хранятся CSS
    
module.exports = Encore.getWebpackConfig();