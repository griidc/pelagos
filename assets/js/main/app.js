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
const React = require('react');
const ReactDom = require('react-dom');
import { Popover, Transition } from '@headlessui/react';
import { ChevronDownIcon } from '@heroicons/react/20/solid';
import clsx from 'clsx';

const el =
  <span>
    <Popover className="relative">
    <Popover.Button>
      Hello!
    </Popover.Button>
    </Popover>
  <Popover className="relative">

    {({ open }) => (
      <React.Fragment>
      <Popover.Button
            className={clsx(
              'flex items-center text-base font-medium ml-8 hover:text-blue-600',
              open ? 'text-blue-600' : 'text-gray-600',
            )}
          >
        Solutions
        <ChevronDownIcon
            className={clsx(
              'h-5 w-5 flex-none text-blue-600',
              open && 'rotate-180',
            )}
            aria-hidden="true"
          />
      </Popover.Button>

      <Transition
            as={React.Fragment}
            enter="transition ease-out duration-200"
            enterFrom="opacity-0 translate-y-1"
            enterTo="opacity-100 translate-y-0"
            leave="transition ease-in duration-150"
            leaveFrom="opacity-100 translate-y-0"
            leaveTo="opacity-0 translate-y-1"
          >
      <Popover.Panel
              static
              className="absolute left-8 top-full z-10 mt-3 w-56 rounded bg-white shadow-md ring-1 ring-gray-100"
            >
              <Popover>

              <Popover.Button
            className={clsx(
              'flex items-center text-base font-medium ml-8 hover:text-blue-600',
              open ? 'text-blue-600' : 'text-gray-600',
            )}
          >
        Something else
        <ChevronDownIcon
            className={clsx(
              'h-5 w-5 flex-none text-blue-600',
              open && 'rotate-180',
            )}
            aria-hidden="true"
          />
      </Popover.Button>
              </Popover>

              <Popover>

              <Popover.Button
            className={clsx(
              'flex items-center text-base font-medium ml-8 hover:text-blue-600',
              open ? 'text-blue-600' : 'text-gray-600',
            )}
          >
        Second

      </Popover.Button>
              </Popover>

      </Popover.Panel>
      </Transition>
      </React.Fragment>
      )}
    </Popover>
    </span>
;

ReactDom.render(el, document.getElementById('test-react'));

global.axios = axios;

const routes = require('../../../public/js/fos_js_routes.json');

Routing.setRoutingData(routes);
global.Routing = Routing;
global.templateSwitch = templateSwitch;

templateSwitch.setTemplate('GRIIDC');

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

$(() => {
  $('#pelagos-menu-1').hoverIntent(hoverIn, hoverOut, 'li');

  $(window).on('resize', () => {
    setContentHeight();
  }).trigger('resize');
});
