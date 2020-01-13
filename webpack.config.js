var Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')

    // public path used by the web server to access the output path
    .setPublicPath(Encore.isProduction() ? '/pelagos-symfony/build' : '/build')

    // only needed for CDN's or sub-directory deploy
    .setManifestKeyPrefix('build/')


    // Enable React
    .enableReactPreset()

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .createSharedEntry('app', './assets/js/app.js')
    .addEntry('layout', './assets/js/layout.js')
    .addEntry('downloadBox', './assets/js/downloadBox.js')
    .addEntry('search-app', './assets/js/search-app.js')

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    //.enableSingleRuntimeChunk()

    // No runtime.js needed.
    .disableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
