import React, { Fragment } from 'react';
import { Popover, Transition } from '@headlessui/react';
import { ChevronDownIcon } from '@heroicons/react/20/solid';
import clsx from 'clsx';

function avoidFocusOnClick() {
  requestAnimationFrame(() => {
    if (document.activeElement instanceof HTMLElement) {
      document.activeElement.blur();
    }
  });
}

const GriidcMenu = () => (
    <Popover.Group className="hidden lg:flex">
      <Popover className="relative">
        <a className="text-base font-medium text-gray-600 ml-8 hover:text-blue-600" href="/">Home</a>
      </Popover>
      <Popover className="relative">
        <a className={clsx(
          'text-base font-medium text-gray-600 ml-8 hover:text-blue-600 font-bold text-blue-200',
        )}
        href="/about">
          About Us
        </a>
      </Popover>
      <Popover className="relative">
        {({ open }) => (
          <Fragment>
            <Popover.Button
              className={clsx(
                'flex items-center text-base font-medium ml-8 hover:text-blue-600',
                open ? 'text-blue-600' : 'text-gray-600',
              )}
              onClick={() => avoidFocusOnClick()}
            >
              Help
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
               <a className={clsx(
                 'm-1.5 block hover:text-blue-600 p-2 text-sm leading-[1.25rem] text-gray-500',
               )}
               href="/faq">
                Frequently Asked Questions
              </a>
              <a className={clsx(
                'm-1.5 block hover:text-blue-600 p-2 text-sm leading-[1.25rem] text-gray-500',
              )}
               href="/training-and-user-guides">
                Training and User Guides
              </a>
              <a className={clsx(
                'm-1.5 block hover:text-blue-600 p-2 text-sm leading-[1.25rem] text-gray-500',
              )}
               href="/webinar-recordings">
                Webinar Recordings
              </a>
              </Popover.Panel>
            </Transition>
          </Fragment>
        )}
      </Popover>
      <Popover className="relative">
        {({ open }) => (
          <Fragment>
            <Popover.Button
              className={clsx(
                'flex items-center text-base font-medium ml-8 hover:text-blue-600',
                open ? 'text-blue-600' : 'text-gray-600',
              )}
              onClick={() => avoidFocusOnClick()}
            >
              Manage Data
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
                 <a className={clsx(
                   'm-1.5 block hover:text-blue-600 p-2 text-sm leading-[1.25rem] text-gray-500',
                 )}
                href="/submit-data">
                  Submit Data
                </a>
                <a className={clsx(
                  'm-1.5 block hover:text-blue-600 p-2 text-sm leading-[1.25rem] text-gray-500',
                )}
                href="/search-data">
                  Search Data
                </a>
                <a className={clsx(
                  'm-1.5 block hover:text-blue-600 p-2 text-sm leading-[1.25rem] text-gray-500',
                )}
                href="/monitor-data">
                  Monitor Data
                </a>
              </Popover.Panel>
            </Transition>
          </Fragment>
        )}
      </Popover>
      <Popover className="relative">
        <a className="text-base font-medium text-gray-600 ml-8 hover:text-blue-600" href="/contact">Contact Us</a>
      </Popover>
    </Popover.Group>
);

export default GriidcMenu;
