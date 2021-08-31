module.exports = {
  env: {
    browser: true,
    es2021: true,
  },
  extends: [
    'plugin:vue/essential',
    'airbnb-base',
  ],
  parserOptions: {
    ecmaVersion: 12,
    sourceType: 'module',
  },
  plugins: [
    'vue',
  ],
  ignorePatterns: [
    'assets/static/js/*.js',
  ],
  rules: {
    'import/no-extraneous-dependencies': ['error', { devDependencies: true }],
    'max-len': ['warn', { code: 120 }],
  },
  settings: {
    'import/resolver': {
      alias: {
        map: [['@', './assets/js']],
        extensions: ['.js', '.vue'],
      },
    },
  },
};
