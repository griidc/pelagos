/*
 * App.js the main app for the base template.
 */

'use strict';

const $ = require('jquery');
global.$ = global.jQuery = $;

require('../css/template.css');
require('../css/superfish.css');
require('../css/pelagos-module.css');
require('../css/messages.css');
const axios = require('axios');
global.axios = axios;
import 'bootstrap';
require('../scss/nas-grp.scss');

const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

Routing.setRoutingData(routes);
global.Routing = Routing;

import templateSwitch from "@/vue/utils/template-switch.js";
global.templateSwitch =  templateSwitch;

function toggleDropdown (event) {
    const dropdown = $(event.target).closest('.dropdown'),
        menu = $('.dropdown-menu', dropdown);
    setTimeout(function() {
        const shouldOpen = event.type !== 'click' && dropdown.is(':hover');
        menu.toggleClass('show', shouldOpen);
        dropdown.toggleClass('show', shouldOpen);
        $('[data-toggle="dropdown"]', dropdown).attr('aria-expanded', shouldOpen);
    }, event.type === 'mouseleave' || event.type === 'mouseenter' ? 300 : 0);
}

$( document ).ready(function() {
    $(".dropdown").hoverIntent(toggleDropdown);
    $(window).resize(function(){
        setContentHeight();
    }).resize();
});

function setContentHeight() {
    var winHeight = $(window).height();
    var adminMenuHeight = $("#admin-menu").outerHeight(true);
    if (adminMenuHeight) {
        $('body').height($(window).height() - adminMenuHeight);
    }
    else {
        adminMenuHeight = 0;
    }
    var headerHeight = $("#header").outerHeight(true);
    var navHeight = $("#navigation").outerHeight(true);
    var messagesHeight = $("#page > .messages").length ? $("#page > .messages").outerHeight(true) : 0;
    var footerHeight = $('#footer').length ? $("#footer").outerHeight(true) : 0;

    var newheight = winHeight - (adminMenuHeight + headerHeight + navHeight + messagesHeight + footerHeight);

    $(".page-pelagos-full #main-wrapper").height(newheight);
}
