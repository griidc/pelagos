const $ = require('jquery');

global.jQuery = $;
global.$ = global.jQuery;
global.queryString = require('query-string');
global.loadingSpinner = require('./spinner');
global.errorDialog = require('./errorDialog');

require('jquery-migrate');

require('@fortawesome/fontawesome-free/css/all.min.css');

require('jquery-ui-themes/themes/smoothness/jquery-ui.min.css');

require('jquery-ui/ui/widgets/autocomplete');
require('jquery-ui/ui/widgets/button');
require('jquery-ui/ui/widgets/datepicker');
require('jquery-ui/ui/widgets/dialog');
require('jquery-ui/ui/widgets/tabs');

require('jquery.cookie');
require('qtip2');
