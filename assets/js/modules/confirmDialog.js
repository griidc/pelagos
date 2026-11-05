import { Modal } from 'flowbite';
import Handlebars from 'handlebars';
import modalTemplate from './templates/confirmModal.hbs';

export default class ConfirmDialog {
  constructor(message) {
    this.modalInstance = null;
    this.modalElement = null;
    const modalOptions = {
      message,
    };

    const newElement = document.createElement('div');
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

    this.modalElement.querySelector('[data-modal-hide="confirm-modal"]').addEventListener('click', () => {
      this.hide();
    });
  }

  show() {
    this.modalInstance.show();
  }

  hide() {
    this.modalInstance.hide();
  }
}
