/*
 * App.js the main app for the base template.
 */

import 'bootstrap';
import templateSwitch from '../vue/utils/template-switch';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';

const $ = require('jquery');

global.jQuery = $;
global.$ = global.jQuery;

require('../../css/template.css');
require('../../css/superfish.css');
require('../../css/pelagos-module.css');
require('../../css/messages.css');
const axios = require('axios');

global.axios = axios;
require('../../scss/hri.scss');

const routes = require('../../../public/js/fos_js_routes.json');

Routing.setRoutingData(routes);
global.Routing = Routing;
global.templateSwitch = templateSwitch;

templateSwitch.setTemplate('HRI');

function toggleDropdown(event) {
  const dropdown = $(event.target).closest('.dropdown');
  const menu = $('.dropdown-menu', dropdown);
  setTimeout(() => {
    const shouldOpen = event.type !== 'click' && dropdown.is(':hover');
    menu.toggleClass('show', shouldOpen);
    dropdown.toggleClass('show', shouldOpen);
    $('[data-toggle="dropdown"]', dropdown).attr('aria-expanded', shouldOpen);
  }, event.type === 'mouseleave' || event.type === 'mouseenter' ? 300 : 0);
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

$(() => {
  $('.dropdown').hoverIntent(toggleDropdown);

  $(window).on('resize', () => {
    setContentHeight();
  }).trigger('resize');
});
