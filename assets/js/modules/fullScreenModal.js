import { Modal } from 'flowbite';
import modalTemplate from './templates/fullScreenModal.hbs';
import Handlebars from 'handlebars';

export default class FullScreenModal {
  constructor(options = {}) {
    this.modalInstance = null;
    this.modalElement = null;
    let cookieName = options.cookieName || 'generic-modal-acknowledged';
    let title = options.title || 'Modal Title Placeholder';
    let content = options.content || 'Modal Content Placeholder';

    const isAcknowledged = document.cookie
      .split(';')
      .map((cookie) => cookie.trim())
      .some((cookie) => cookie.startsWith(`${cookieName}=`));

    if (isAcknowledged) {
      return;
    }

    const newElement = document.createElement('div');

    let modalOptions = {
      title: title,
      content: content,
    };
    newElement.innerHTML = Handlebars.compile(modalTemplate)(modalOptions);

    this.modalElement = newElement.firstChild;
    document.body.appendChild(this.modalElement);

    this.modalInstance = new Modal(
      this.modalElement,
      {
        backdrop: 'dynamic',
        placement: 'center',
        closable: false,
      },
      {
        id: 'modalEl',
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
    const activeElement = document.activeElement;
    if (activeElement && this.modalElement?.contains(activeElement) && typeof activeElement.blur === 'function') {
      activeElement.blur();
    }
    this.modalInstance?.hide();
  }
}
