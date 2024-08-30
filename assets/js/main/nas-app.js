/*
 * App.js the main app for the base template.
 */

import React from 'react';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';
import { createRoot } from 'react-dom/client';
import 'bootstrap';
import templateSwitch from '../vue/utils/template-switch';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';
import GriidcMenu from '../react/components/GriidcMenu';

window.Alpine = Alpine;
window.tippy = tippy;

Alpine.plugin(collapse);
Alpine.start();

const $ = require('jquery');

global.jQuery = $;
global.$ = global.jQuery;

require('../../css/template.css');
require('../../css/superfish.css');
require('../../css/pelagos-module.css');
require('../../css/messages.css');
const axios = require('axios');

const mainsite = process.env.MAINSITE;
const griidcMenuElement = document.getElementById('griidc-menu');
const showAdmin = griidcMenuElement.hasAttribute('show-admin') && griidcMenuElement.getAttribute('show-admin') === 'true';

createRoot(griidcMenuElement).render(<GriidcMenu mainsite={mainsite} showAdmin={ showAdmin }/>);

global.axios = axios;
require('../../scss/nas-grp.scss');

const routes = require('../../../public/js/fos_js_routes.json');

Routing.setRoutingData(routes);
global.Routing = Routing;
global.templateSwitch = templateSwitch;

templateSwitch.setTemplate('GRP');

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
