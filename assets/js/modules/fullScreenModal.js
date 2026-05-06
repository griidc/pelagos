import { Modal } from 'flowbite';
import modalTemplate from './templates/fullScreenModal.hbs';
import Handlebars from 'handlebars';

export default class FullScreenModal {
  constructor(options = {}) {
    this.modalInstance = null;
    this.cookieName = options.cookieName || 'generic-modal-acknowledged';
    this.title = options.title || 'Modal Title Placeholder';
    this.content = options.content || 'Modal Content Placeholder';

    const isAcknowledged = document.cookie
      .split(';')
      .map((cookie) => cookie.trim())
      .some((cookie) => cookie.startsWith(`${this.cookieName}=`));

    if (isAcknowledged) {
      return;
    }

    const newElement = document.createElement('div');
    newElement.innerHTML = Handlebars.compile(modalTemplate)({ title: this.title, content: this.content });
    const modalElement = newElement.firstChild;
    document.body.appendChild(modalElement);

    const fontSource = document.getElementById('pelagos-content') || document.body;
    modalElement.style.fontFamily = window.getComputedStyle(fontSource).fontFamily;

    // Ensure full-width/full-height placement even if page-level CSS overrides utility classes.
    Object.assign(modalElement.style, {
      position: 'fixed',
      left: '50px',
      right: '50px',
      bottom: '50px',
      top: '50px',
      width: 'calc(100vw - 100px)',
      height: 'calc(100vh - 100px)',
      zIndex: '50',
      overflow: 'auto',
    });

    const modalInstance = new Modal(
      modalElement,
      {
        backdrop: 'dynamic',
        placement: 'center',
        closable: true,
      },
      {
        id: 'fullscreen-modal',
        override: true,
      },
    );

    const showModal = () => {
      modalInstance.show();
    };

    modalElement.querySelector('#modal-close-button').addEventListener('click', () => {
      modalInstance.hide();
    });

    modalElement.querySelector('#modal-close-button-for-good').addEventListener('click', () => {
      modalInstance.hide();
      document.cookie = `${this.cookieName}=1; path=/; max-age=31536000; SameSite=Lax`;
    });

    showModal();
  }
}
