const Encore = require('@symfony/webpack-encore');
const path = require('path');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
// directory where compiled assets will be stored
  .setOutputPath('public/build/')

// public path used by the web server to access the output path
  .setPublicPath(process.env.publicpath ? process.env.publicpath : '/build')

// only needed for CDN's or sub-directory deploy
  .setManifestKeyPrefix('build/')

// Enable Vue js
  .enableVueLoader(() => {}, {
    runtimeCompilerBuild: false,
  })

/*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
  .addEntry('app', './assets/js/main/app.js')
  .addEntry('layout', './assets/js/layout.js')
  .addEntry('downloadBox', './assets/js/downloadBox.js')
  .addEntry('search-app', './assets/js/search.js')
  .addEntry('nas-app', './assets/js/main/nas-app.js')
  .addEntry('research-group', './assets/js/research-group.js')
  .addEntry('grp-home', './assets/js/grp-home.js')
  .addEntry('stats', './assets/js/stats.js')
  .addEntry('file-manager', './assets/js/file-manager.js')
  .addEntry('person-profile', './assets/js/entry/person-profile.js')
  .addEntry('how-to-submit-data', './assets/js/entry/how-to-submit-data.js')
  .addEntry('data-land', './assets/js/entry/data-land.js')
  .addEntry('hri-app', './assets/js/main/hri-app.js')
  .addEntry('information-product', './assets/js/entry/information-product.js')
  .addEntry('information-product-list', './assets/js/entry/information-product-list.js')

  // enables Sass/SCSS support
  .enableSassLoader()
  .enablePostCssLoader()

  // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
  // .enableStimulusBridge('./assets/controllers.json')

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()

  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()

  .addAliases({
    '@': path.resolve(__dirname, 'assets', 'js'),
    images: path.resolve(__dirname, 'assets', 'images'),
    vue: Encore.isProduction() ? 'vue/dist/vue.min.js' : 'vue/dist/vue.js',
  })

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

  .configureBabel((config) => {
    config.plugins.push('@babel/plugin-proposal-class-properties');
})

  // enables @babel/preset-env polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = 'usage';
    config.corejs = 3;
  })

  // uncomment if you use TypeScript
  //.enableTypeScriptLoader()

  // uncomment if you use React
  //.enableReactPreset()

  // uncomment to get integrity="..." attributes on your script & link tags
  // requires WebpackEncoreBundle 1.4 or higher
  //.enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
  .autoProvidejQuery()

  .copyFiles(
    {
      from: './assets/static',
      to: '[path][name].[hash:8].[ext]',
      includeSubdirectories: true,
    },
  )

  .enableIntegrityHashes();
module.exports = Encore.getWebpackConfig();
