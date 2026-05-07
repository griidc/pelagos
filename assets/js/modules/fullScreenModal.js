import { Modal } from 'flowbite';
import modalTemplate from './templates/fullScreenModal.hbs';
import Handlebars from 'handlebars';

export default class FullScreenModal {
  constructor(options = {}) {
    this.modalInstance = null;
    this.cookieName = options.cookieName || 'generic-modal-acknowledged';
    this.title = options.title || 'Modal Title Placeholder';
    this.content = options.content || 'Modal Content Placeholder';
    this.supplementalCss = options.supplementalCss || null;

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

    this.applySupplementalCss(modalElement);

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
      this.hideModal(modalElement, modalInstance);
    });

    modalElement.querySelector('#modal-close-button-for-good').addEventListener('click', () => {
      this.hideModal(modalElement, modalInstance);
      document.cookie = `${this.cookieName}=1; path=/; max-age=31536000; SameSite=Lax`;
    });

    showModal();
  }

  applySupplementalCss(modalElement) {
    if (typeof this.supplementalCss === 'string') {
      modalElement.style.cssText += this.supplementalCss;
      return;
    }

    if (!this.supplementalCss || typeof this.supplementalCss !== 'object') {
      return;
    }

    const supplementalCssKeys = Object.keys(this.supplementalCss);
    const hasSelectorRules = supplementalCssKeys.some((key) => /[.#:[\s>+~]/.test(key));

    if (!hasSelectorRules) {
      Object.assign(modalElement.style, this.supplementalCss);
      return;
    }

    supplementalCssKeys.forEach((selector) => {
      const ruleSet = this.supplementalCss[selector];
      if (!ruleSet || typeof ruleSet !== 'object') {
        return;
      }

      modalElement.querySelectorAll(selector).forEach((element) => {
        Object.assign(element.style, ruleSet);
      });
    });
  }

  hideModal(modalElement, modalInstance) {
    const activeElement = document.activeElement;
    if (activeElement && modalElement.contains(activeElement) && typeof activeElement.blur === 'function') {
      activeElement.blur();
    }

    modalInstance.hide();
  }
}
