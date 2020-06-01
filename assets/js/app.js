/*
 * App.js the main app for the base template.
 */

'use strict';

const $ = require('jquery');
global.$ = global.jQuery = $;


require('../css/template.css');
require('../css/jira-buttons.css');
require('../css/superfish-navbar.css');
require('../css/superfish.css');
require('../css/pelagos-module.css');
require('../css/messages.css');
require('../css/griidc-app.css');
import '../scss/griidc.scss';

const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

Routing.setRoutingData(routes);
global.Routing = Routing;

$( document ).ready(function() {
    $("#pelagos-menu-1").hoverIntent(hoverIn, hoverOut, 'li');

    $(window).resize(function(){
        setContentHeight();
    }).resize();
});

function hoverIn() {
    $(this).find("ul").removeClass("sf-hidden");
    $(this).addClass("sfHover");
}

function hoverOut() {
    $(this).find("ul").addClass("sf-hidden");
    $(this).removeClass("sfHover");
}

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


