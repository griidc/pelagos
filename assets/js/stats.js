const $ = require('jquery');
global.$ = global.jQuery = $;

import '../css/stats.css';

import 'bootstrap';
import Vue from 'vue';
// import DatasetOverTime from './vue/components/stats/DatasetOverTime';
// import DatasetSizeRanges from './vue/components/stats/DatasetSizeRanges';
import StatsApp from './vue/Stats.vue';

var flotConfig;
var overviewSections;
var style = getComputedStyle(document.body);
// var theme = {};

// theme.primary = style.getPropertyValue('--primary');
// theme.secondary = style.getPropertyValue('--secondary');
// theme.alternate = style.getPropertyValue('--alternate');

// console.log(theme);

// getComputedStyle(document.documentElement)
    // .getPropertyValue('--my-variable-name');

new Vue({
  el: '#stats',
  components: { StatsApp },
  template: '<StatsApp/>'
});
