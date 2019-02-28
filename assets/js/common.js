'use strict';

const $ = require('jquery');
global.$ = global.jQuery = $;

require('../css/template.css');
require('../css/superfish-navbar.css');
require('../css/superfish.css');
require('../css/pelagos-module.css');

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

