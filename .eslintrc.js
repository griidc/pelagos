module.exports = {
  env: {
    browser: true,
    es2021: true,
  },
  extends: [
    'plugin:vue/essential',
    'airbnb-base',
    'plugin:react/recommended',
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
    'import/extensions': [
      'warn',
      'always',
      {
        js: 'never',
        vue: 'never',
      },
    ],
  },
  settings: {
    "react": {
      "version": "detect", // React version. "detect" automatically picks the version you have installed.
    },
    'import/resolver': {
      alias: {
        map: [['@', './assets/js']],
        extensions: ['.js', '.vue'],
      },
    },
  },
};
