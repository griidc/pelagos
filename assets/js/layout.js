'use strict';

const $ = require('jquery');

global.$ = global.jQuery = $;

require('jquery-ui/themes/base/minified/jquery-ui.min.css');

require('jquery-ui/autocomplete');
require('jquery-ui/button');
require('jquery-ui/datepicker');
require('jquery-ui/dialog');
require('jquery-ui/tabs');
require('jquery-ui/widget');
require('jquery.cookie');
require('qtip2');

$(document).ready(function() {
   console.log('Webpack Loaded');
});