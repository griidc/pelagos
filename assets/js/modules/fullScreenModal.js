import { Modal } from 'flowbite';
import Handlebars from 'handlebars';
import modalTemplate from './templates/fullScreenModal.hbs';

export default class FullScreenModal {
  constructor(options = {}) {
    this.modalInstance = null;
    this.modalElement = null;
    const cookieName = options.cookieName || 'generic-modal-acknowledged';
    const title = options.title || 'Modal Title Placeholder';
    const content = options.content || 'Modal Content Placeholder';

    const isAcknowledged = document.cookie
      .split(';')
      .map((cookie) => cookie.trim())
      .some((cookie) => cookie.startsWith(`${cookieName}=`));

    if (isAcknowledged) {
      return;
    }

    const newElement = document.createElement('div');

    const modalOptions = {
      title,
      content,
    };
    newElement.innerHTML = Handlebars.compile(modalTemplate)(modalOptions);

    this.modalElement = newElement.firstChild;
    document.body.appendChild(this.modalElement);

    this.modalInstance = new Modal(
      this.modalElement,
      {
        backdrop: 'static',
        placement: 'center',
        closable: false,
        backdropClasses: 'z-[9998] bg-gray-900/70 fixed inset-0',
      },
      {
        id: 'popup-modal',
        override: true,
      },
    );

    this.modalElement.querySelector('#modal-close-button').addEventListener('click', () => {
      this.hide();
    });

    this.modalElement.querySelector('#modal-close-button-for-good').addEventListener('click', () => {
      this.hide();
      document.cookie = `${cookieName}=1; path=/; max-age=31536000; SameSite=Lax`;
    });
  }

  show() {
    this.modalInstance?.show();
  }

  hide() {
    // blur any active element inside the modal to prevent focus issues after hiding
    // for screenreaders and keyboard users
    const { activeElement } = document;
    if (activeElement && this.modalElement?.contains(activeElement) && typeof activeElement.blur === 'function') {
      activeElement.blur();
    }
    this.modalInstance?.hide();
  }
}
