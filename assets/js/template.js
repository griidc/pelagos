'use strict';

const $ = require('jquery');
require('../css/template.css');
require('../css/superfish-navbar.css');
require('../css/superfish.css');
require('../css/pelagos-module.css');


$( document ).ready(function() {
    $("#pelagos-menu-1").hoverIntent(hoverIn, hoverOut, 'li');
});

function hoverIn() {
    $(this).find("ul").removeClass("sf-hidden");
    $(this).addClass("sfHover");
}

function hoverOut() {
    $(this).find("ul").addClass("sf-hidden");
    $(this).removeClass("sfHover");
}