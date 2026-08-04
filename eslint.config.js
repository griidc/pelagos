const {
    defineConfig,
    globalIgnores,
} = require("eslint/config");

const globals = require("globals");
const vue = require("eslint-plugin-vue");
const js = require("@eslint/js");

const {
    FlatCompat,
} = require("@eslint/eslintrc");

const compat = new FlatCompat({
    baseDirectory: __dirname,
    recommendedConfig: js.configs.recommended,
    allConfig: js.configs.all
});

module.exports = defineConfig([{
    languageOptions: {
        globals: {
            ...globals.browser,
        },

        ecmaVersion: 12,
        sourceType: "module",
        parserOptions: {},
    },

    extends: compat.extends("plugin:vue/essential", "airbnb-base", "plugin:react/recommended"),

    plugins: {
        vue,
    },

    rules: {
        "import/no-extraneous-dependencies": ["error", {
            devDependencies: true,
        }],

        "max-len": ["warn", {
            code: 120,
        }],

        "import/extensions": ["warn", "always", {
            js: "never",
            vue: "never",
        }],
    },

    settings: {
        "react": {
            "version": "detect",
        },

        "import/resolver": {
            alias: {
                map: [["@", "./assets/js"]],
                extensions: [".js", ".vue"],
            },
        },
    },
}, globalIgnores(["assets/static/js/*.js"])]);
