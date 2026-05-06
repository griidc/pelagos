import { Modal } from 'flowbite';
import modalTemplate from './templates/fullScreenModal.hbs';
import Handlebars from 'handlebars';

export default class FullScreenModal {
  constructor(options = {}) {
    this.modalInstance = null;
    this.cookieName = options.cookieName || 'newdifack';
    this.title = options.title || 'Modal';
    this.content = options.content || 'Some content.';

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

    // Ensure full-width/full-height placement even if page-level CSS overrides utility classes.
    Object.assign(modalElement.style, {
      position: 'fixed',
      left: '10 rem',
      right: '10 rem',
      bottom: '10 rem',
      top: 'var(--headerHeight, 110px)',
      width: 'calc(100vw - 20px)',
      height: 'calc(100vh - var(--headerHeight, 110px) - 20px)',
      zIndex: '50',
      backgroundColor: 'var(--color-light-cyan, #d8f2fa)',
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
