/*
 * App.js the main app for the base template.
 */

import '../../scss/griidc.scss';
import templateSwitch from '../vue/utils/template-switch';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';

const $ = require('jquery');

global.jQuery = $;
global.$ = global.jQuery;
require('../../css/template.css');
require('../../css/jira-buttons.css');
require('../../css/superfish-navbar.css');
require('../../css/superfish.css');
require('../../css/pelagos-module.css');
require('../../css/messages.css');
require('../../css/griidc-app.css');
const axios = require('axios');

global.axios = axios;

const routes = require('../../../public/js/fos_js_routes.json');

Routing.setRoutingData(routes);
global.Routing = Routing;
global.templateSwitch = templateSwitch;

function hoverIn() {
  $(this).find('ul').removeClass('sf-hidden');
  $(this).addClass('sfHover');
}

function hoverOut() {
  $(this).find('ul').addClass('sf-hidden');
  $(this).removeClass('sfHover');
}

function setContentHeight() {
  const winHeight = $(window).height();
  let adminMenuHeight = $('#admin-menu').outerHeight(true);
  if (adminMenuHeight) {
    $('body').height($(window).height() - adminMenuHeight);
  } else {
    adminMenuHeight = 0;
  }
  const headerHeight = $('#header').outerHeight(true);
  const navHeight = $('#navigation').outerHeight(true);
  const messagesHeight = $('#page > .messages').length ? $('#page > .messages').outerHeight(true) : 0;
  const footerHeight = $('#footer').length ? $('#footer').outerHeight(true) : 0;

  const newheight = winHeight
      - (adminMenuHeight + headerHeight + navHeight + messagesHeight + footerHeight);

  $('.page-pelagos-full #main-wrapper').height(newheight);
}

function chicagoToLocalHour(chicagoHour) {
  // messy stuff goes here
  const localHour = chicagoHour;
  return localHour;
}

function maintenanceWindowAlert(windowDay, chicagoHour, duration) {
  (function loop() {
    let now = new Date();

    const year = now.getUTCFullYear();
    const monthIndex = now.getUTCMonth();
    const date = now.getUTCDate();
    const day = now.getDay();

    const hour = chicagoToLocalHour(chicagoHour);

    const startingWindowTimestamp = (new Date(year, monthIndex, date, hour).getTime());
    const endingWindowTimestamp = startingWindowTimestamp + 3600 * 1000 * duration;

    if (windowDay === day && Date.now() >= startingWindowTimestamp && Date.now() < endingWindowTimestamp) {
      // eslint-disable-next-line no-console
      console.log("We're inside maintenance window.");
    } else {
      // eslint-disable-next-line no-console
      console.log('not in maintenance window');
    }

    now = new Date(); // allow for time passing
    const delay = 60000 - (now % 60000); // exact ms to next minute interval
    setTimeout(loop, delay);
  }());
}

$(document).ready(() => {
  $('#pelagos-menu-1').hoverIntent(hoverIn, hoverOut, 'li');
  // tuesday = 2, hr=15 (maintenance starting hr in America/Chicago time)
  maintenanceWindowAlert(2, 15, 2);

  $(window).resize(() => {
    setContentHeight();
  }).resize();
});
