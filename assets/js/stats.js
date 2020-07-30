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
var theme = {};

theme.main = style.getPropertyValue('--color-main');
theme.secondary = style.getPropertyValue('--color-menu');
theme.dark = style.getPropertyValue('--color-headerMiddle');
theme.light = style.getPropertyValue('--color-headerTop');

// getComputedStyle(document.documentElement)
    // .getPropertyValue('--my-variable-name');

new Vue({
  el: '#stats',
  components: { StatsApp },
  template: '<StatsApp/>'
});
