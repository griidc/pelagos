import React, { Fragment } from 'react';
import { Popover, Transition, Disclosure } from '@headlessui/react';
import { ChevronDownIcon } from '@heroicons/react/20/solid';
import clsx from 'clsx';
import { PropTypes } from 'prop-types';

function avoidFocusOnClick() {
  requestAnimationFrame(() => {
    if (document.activeElement instanceof HTMLElement) {
      document.activeElement.blur();
    }
  });
}

const GriidcMenu = ({ mainsite, showAdmin = false }) => (
    <Popover.Group className="hidden lg:flex">
      <Popover className="relative">
        <a className="text-base font-medium text-gray-600 ml-8 hover:text-blue-600" href="/">Home</a>
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
              About Us
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
                href={`${mainsite}/about`}>
                About Us
              </a>
              <a className={clsx(
                'm-1.5 block hover:text-blue-600 p-2 text-sm leading-[1.25rem] text-gray-500',
              )}
                href={`${mainsite}/program-overview`}>
                Program Overview
              </a>
              <a className={clsx(
                'm-1.5 block hover:text-blue-600 p-2 text-sm leading-[1.25rem] text-gray-500',
              )}
               href={`${mainsite}/team`}>
                Our Team
              </a>
              <a className={clsx(
                'm-1.5 block hover:text-blue-600 p-2 text-sm leading-[1.25rem] text-gray-500',
              )}
               href={`${mainsite}/griidc-services`}>
                Services
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
                <Disclosure as="div" className="">
                  {({
                    // eslint-disable-next-line no-shadow
                    open,
                  }) => (
                    <div className={clsx('m-1.5 rounded', open && 'bg-gray-100')}>
                      <Disclosure.Button
                        className={clsx(
                          'flex w-full items-center justify-between px-2 py-1 text-sm leading-[1.25rem] hover:text-blue-600',
                          open ? 'text-blue-600 font-medium' : 'text-gray-500',
                        )}
                      >
                        Submit Data
                        <ChevronDownIcon
                          className={clsx('h-5 w-5 flex-none text-blue-600', {
                            'rotate-180': open,
                          })}
                          aria-hidden="true"
                        />
                      </Disclosure.Button>
                      <Disclosure.Panel className="">
                        <a className={clsx(
                          'block p-2 pl-5 text-sm leading-[1.25rem] text-gray-500 hover:text-blue-600',
                        )}
                        href={`${mainsite}/how-submit-data`}>
                          How to Submit Data
                        </a>
                        <a className={clsx(
                          'block p-2 pl-5 text-sm leading-[1.25rem] text-gray-500 hover:text-blue-600',
                        )}
                        href="/dif">
                          Dataset Information Form
                        </a>
                        <a className={clsx(
                          'block p-2 pl-5 text-sm leading-[1.25rem] text-gray-500 hover:text-blue-600',
                        )}
                        href="/dataset-submission">
                          Dataset Submission
                        </a>
                      </Disclosure.Panel>
                    </div>
                  )}
                </Disclosure>
                <a className={clsx(
                  'm-1.5 block hover:text-blue-600 p-2 text-sm leading-[1.25rem] text-gray-500',
                )}
                href="/search">
                  Search Data
                </a>
                <a className={clsx(
                  'm-1.5 block hover:text-blue-600 p-2 text-sm leading-[1.25rem] text-gray-500',
                )}
                href="/dataset-monitoring">
                  Monitor Data
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
                href="/account">
                Account Request
              </a>
              <a className={clsx(
                'm-1.5 block hover:text-blue-600 p-2 text-sm leading-[1.25rem] text-gray-500',
              )}
                href={`${mainsite}/faq`}>
                Frequently Asked Questions
              </a>
              <a className={clsx(
                'm-1.5 block hover:text-blue-600 p-2 text-sm leading-[1.25rem] text-gray-500',
              )}
               href={`${mainsite}/training-user-guides`}>
                Training and User Guides
              </a>
              <a className={clsx(
                'm-1.5 block hover:text-blue-600 p-2 text-sm leading-[1.25rem] text-gray-500',
              )}
               href={`${mainsite}/webinar-recordings`}>
                Webinar Recordings
              </a>
              <a className={clsx(
                'm-1.5 block hover:text-blue-600 p-2 text-sm leading-[1.25rem] text-gray-500',
              )}
               href={`${mainsite}/data-file-transfer-methods`}>
                Data File Transfer Methods
              </a>
              </Popover.Panel>
            </Transition>
          </Fragment>
        )}
      </Popover>
      <Popover className="relative">
        <a className="text-base font-medium text-gray-600 ml-8 hover:text-blue-600"
          href={`${mainsite}/contact`}>Contact Us</a>
      </Popover>
      { showAdmin
        && <Popover className="relative">
          <a className="text-base font-medium text-gray-600 ml-8 hover:text-blue-600"
            href="/admin">Admin</a>
        </Popover>
      }
    </Popover.Group>
);

GriidcMenu.propTypes = {
  mainsite: PropTypes.string,
  showAdmin: PropTypes.bool,
};

export default GriidcMenu;
