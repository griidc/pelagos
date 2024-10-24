/*
 * App.js the main app for the base template.
 */

import React from 'react';
import { createRoot } from 'react-dom/client';
import '../../scss/griidc.scss';
import templateSwitch from '../vue/utils/template-switch';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';
import GriidcMenu from '../react/components/GriidcMenu';

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

const mainsite = process.env.MAINSITE;
const griidcMenu = document.getElementById('griidc-menu');

if (typeof (griidcMenu) !== 'undefined' && griidcMenu != null) {
  const showAdmin = griidcMenu.hasAttribute('show-admin') && griidcMenu.getAttribute('show-admin') === 'true';
  createRoot(griidcMenu).render(<GriidcMenu mainsite={mainsite} showAdmin={ showAdmin }/>);
}

global.axios = axios;

const routes = require('../../../public/js/fos_js_routes.json');

Routing.setRoutingData(routes);
global.Routing = Routing;
global.templateSwitch = templateSwitch;

templateSwitch.setTemplate('GRIIDC');
