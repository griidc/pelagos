const vex = require('vex-js');
// eslint-disable-next-line import/no-extraneous-dependencies
const dialog = require('vex-dialog');

global.vex = vex;
vex.registerPlugin(dialog);
vex.defaultOptions.className = 'vex-theme-os';
