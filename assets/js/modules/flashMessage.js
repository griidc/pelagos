import { Dismiss } from 'flowbite';

const flashStyles = {
  success: {
    bg: 'bg-green-50',
    text: 'text-green-900',
    icon: 'fa-solid fa-circle-check',
  },
  warning: {
    bg: 'bg-yellow-50',
    text: 'text-yellow-900',
    icon: 'fa-solid fa-triangle-exclamation',
  },
  error: {
    bg: 'bg-rose-50',
    text: 'text-rose-900',
    icon: 'fa-solid fa-circle-xmark',
  },
  default: {
    bg: 'bg-blue-50',
    text: 'text-blue-900',
    icon: 'fa-solid fa-circle-info',
  },
};

const htmlContent = (type, message, style, flashIndex) => `
  <div id="flash-banner-${flashIndex}" class="flex items-center p-4 mb-4 text-lg rounded-lg ${style.bg} ${style.text}" role="alert">
    <i class="${style.icon} shrink-0 w-4 h-4 me-3" aria-hidden="true"></i>
    <span class="sr-only">${type}</span>
    <div>
        ${message}
    </div>
    <button
        type="button"
        class="ms-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 p-1.5 inline-flex items-center justify-center h-8 w-8 hover:bg-black/5"
        data-dismiss-target="#flash-banner-${flashIndex}"
        aria-label="Close"
    >
        <span class="sr-only">Close banner</span>
        <i class="fa-solid fa-xmark w-3 h-3" aria-hidden="true"></i>
    </button>
  </div>
`;

export default class FlashMessage {
  /**
   * @param {string|HTMLElement} target The target element or element id of the alert DIV.
   * @param {string|null} message The message to display in the flash alert.
   * @param {string|null} type The type of flash message (success, warning, error, default, info).
   */
  constructor(target, message = '', type = 'info') {
    const parentElement = typeof target === 'string' ? document.getElementById(target) : target;
    const style = flashStyles[type] || flashStyles.default;
    const flashIndex = Math.random().toString(36).substring(2, 8);
    let targetEl;
    let triggerEl;
    let dismiss;

    this.destroy = () => {
      if (dismiss) {
        dismiss.destroyAndRemoveInstance();
      }
      targetEl = null;
      triggerEl = null;
      dismiss = null;
    };

    this.show = () => {
      if (targetEl) return;
      const fragment = document.createRange().createContextualFragment(htmlContent(type, message, style, flashIndex));
      parentElement.appendChild(fragment);
      targetEl = parentElement.querySelector(`#flash-banner-${flashIndex}`);
      triggerEl = targetEl.querySelector('[data-dismiss-target]');
      dismiss = new Dismiss(targetEl, triggerEl);
      triggerEl.addEventListener('click', () => {
        this.destroy();
      });
    };
  }
}
