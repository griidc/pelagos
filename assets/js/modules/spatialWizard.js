import { Modal } from 'flowbite';
import * as turf from '@turf/turf';

import modalTemplate from './templates/spatialWizard.html';

let map = null;
let geoViz = null;

export default class SpatialWizard {
  constructor(geoVizInstance) {
    geoViz = geoVizInstance;
    map = geoVizInstance.map;
    this.modalInstance = null;

    const newElement = document.createElement('div');
    newElement.innerHTML = modalTemplate;
    const modalElement = newElement.firstChild;
    document.body.appendChild(modalElement);

    const modalInstance = new Modal(
      modalElement,
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

    const showWizard = () => {
      if (geoViz.isFullScreen()) {
        geoViz.toggleFullScreen();
      }
      modalInstance.show();
    };

    const hideWizard = () => {
      modalInstance.hide();
    };

    modalElement.querySelector('#modal-close-button').addEventListener('click', () => {
      hideWizard();
    });

    modalElement.querySelector('#point-form').addEventListener('submit', (e) => {
      e.preventDefault();
      hideWizard();
      const formElement = document.getElementById('point-form');
      const formData = new FormData(formElement);
      const lat = formData.get('latitude');
      const lng = formData.get('longitude');
      const point = turf.point([parseFloat(lng), parseFloat(lat)]);
      geoViz.addFeature(point);
      formElement.reset();
    });

    modalElement.querySelector('#bounding-box-form').addEventListener('submit', (e) => {
      e.preventDefault();
      hideWizard();

      const formElement = document.getElementById('bounding-box-form');
      const formData = new FormData(formElement);
      const north = formData.get('north');
      const south = formData.get('south');
      const east = formData.get('east');
      const west = formData.get('west');
      const bboxPolygon = turf.bboxPolygon([parseFloat(west), parseFloat(south), parseFloat(east), parseFloat(north)]);
      geoViz.addFeature(bboxPolygon);
      formElement.reset();
    });

    this.init = () => {};

    map.pm.Toolbar.createCustomControl({
      name: 'Paste',
      block: 'options',
      title: 'Paste Wizard',
      className: 'custom-pm-icon-brush',
      onClick: () => {
        map.pm.Toolbar.buttons.Paste.toggle();
        showWizard();
      },
    });

    map.pm.Toolbar.changeControlOrder([
      'Home',
      'Paste',
    ]);
  }
}
