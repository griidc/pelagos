import { Modal } from 'flowbite';
import * as turf from '@turf/turf';
import JustValidate from 'just-validate';

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

    const pointForm = modalElement.querySelector('#point-form');
    const bboxForm = modalElement.querySelector('#bounding-box-form');
    const textPasteForm = modalElement.querySelector('#text-paste-form');

    const pointFormValidate = new JustValidate(pointForm, {
      errorLabelStyle: {
        color: '#b81111',
        fontWeight: 'bold',
      },
    });

    pointFormValidate
      .addField('#latitude', [
        {
          rule: 'required',
          errorMessage: 'Latitude is required.',
        },
        {
          rule: 'number',
          errorMessage: 'Latitude must be a number.',
        },
        {
          validator: (value) => {
            const num = parseFloat(value);
            return num >= -90 && num <= 90;
          },
          errorMessage: 'Latitude must be between -90 and 90.',
        },
      ], {
        errorsContainer: '#latitude-error',
      })
      .addField('#longitude', [
        {
          rule: 'required',
          errorMessage: 'Longitude is required.',
        },
        {
          rule: 'number',
          errorMessage: 'Longitude must be a number.',
        },
        {
          validator: (value) => {
            const num = parseFloat(value);
            return num >= -180 && num <= 180;
          },
          errorMessage: 'Longitude must be between -180 and 180.',
        },
      ], {
        errorsContainer: '#longitude-error',
      })
      .onSuccess((event) => {
        hideWizard();
        const formElement = event.srcElement;
        const formData = new FormData(formElement);
        const lat = formData.get('latitude');
        const lng = formData.get('longitude');
        const point = turf.point([parseFloat(lng), parseFloat(lat)]);
        geoViz.addFeature(point);
        formElement.reset();
      });

    const bboxFormValidate = new JustValidate(bboxForm, {
      errorLabelStyle: {
        color: '#b81111',
        fontWeight: 'bold',
      },
    });

    bboxFormValidate
      .addField('#north', [
        {
          rule: 'required',
          errorMessage: 'North latitude is required.',
        },
        {
          rule: 'number',
          errorMessage: 'North latitude must be a number.',
        },
        {
          validator: (value) => {
            const num = parseFloat(value);
            return num >= -90 && num <= 90;
          },
          errorMessage: 'North latitude must be between -90 and 90.',
        },
      ])
      .addField('#south', [
        {
          rule: 'required',
          errorMessage: 'South latitude is required.',
        },
        {
          rule: 'number',
          errorMessage: 'South latitude must be a number.',
        },
        {
          validator: (value) => {
            const num = parseFloat(value);
            return num >= -90 && num <= 90;
          },
          errorMessage: 'South latitude must be between -90 and 90.',
        },
      ])
      .addField('#east', [
        {
          rule: 'required',
          errorMessage: 'East longitude is required.',
        },
        {
          rule: 'number',
          errorMessage: 'East longitude must be a number.',
        },
        {
          validator: (value) => {
            const num = parseFloat(value);
            return num >= -180 && num <= 180;
          },
          errorMessage: 'East longitude must be between -180 and 180.',
        },
      ])
      .addField('#west', [
        {
          rule: 'required',
          errorMessage: 'West longitude is required.',
        },
        {
          rule: 'number',
          errorMessage: 'West longitude must be a number.',
        },
        {
          validator: (value) => {
            const num = parseFloat(value);
            return num >= -180 && num <= 180;
          },
          errorMessage: 'West longitude must be between -180 and 180.',
        },
      ])
      .onSuccess((event) => {
        hideWizard();
        const formElement = event.srcElement;
        const formData = new FormData(formElement);
        const north = formData.get('north');
        const south = formData.get('south');
        const east = formData.get('east');
        const west = formData.get('west');
        const bboxPolygon = turf.bboxPolygon([
          parseFloat(west),
          parseFloat(south),
          parseFloat(east),
          parseFloat(north),
        ]);
        geoViz.addFeature(bboxPolygon);
        formElement.reset();
      });

    const textPasteFormValidate = new JustValidate(textPasteForm, {
      errorLabelStyle: {
        color: '#b81111',
        fontWeight: 'bold',
      },
    });

    textPasteFormValidate
      .addField('#coordinates', [
        {
          rule: 'required',
          errorMessage: 'Coordinates are required.',
        },
      ])
      .addRequiredGroup('#feature-type', 'Feature type is required.', {
        errorsContainer: '#feature-type-error',
      })
      .onSuccess((event) => {
        hideWizard();

        const formElement = event.srcElement;
        const formData = new FormData(formElement);
        const coordinatesText = formData.get('coordinates');
        const featureType = formData.get('feature-type');
        const pattern = /[\s,]+/g;
        const coordinateArray = coordinatesText.split(pattern);
        // convert coords into array of [lng, lat] pairs
        let coordinatePairs = [];
        for (let i = 0; i < coordinateArray.length; i += 2) {
          const lat = parseFloat(coordinateArray[i]);
          const lng = parseFloat(coordinateArray[i + 1]);
          if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
            coordinatePairs.push([lng, lat]);
          }
        }

        // if featureType is Polygon, add first coordinate pair to the end of the array to close the polygon
        if (featureType === 'Polygon') {
          coordinatePairs.push(coordinatePairs[0]);
          coordinatePairs = [coordinatePairs];
        }

        const geometry = {
          type: featureType,
          coordinates: coordinatePairs,
        };
        const featureCollection = turf.feature(geometry);
        geoViz.addFeature(featureCollection);
        formElement.reset();
      });

    // pointForm.addEventListener('submit', (e) => {
    //   e.preventDefault();
    //   hideWizard();
    //   const formElement = document.getElementById('point-form');
    //   const formData = new FormData(formElement);
    //   const lat = formData.get('latitude');
    //   const lng = formData.get('longitude');
    //   const point = turf.point([parseFloat(lng), parseFloat(lat)]);
    //   geoViz.addFeature(point);
    //   formElement.reset();
    // });

    // bboxForm.addEventListener('submit', (e) => {
    //   e.preventDefault();
    //   hideWizard();

    //   const formElement = document.getElementById('bounding-box-form');
    //   const formData = new FormData(formElement);
    //   const north = formData.get('north');
    //   const south = formData.get('south');
    //   const east = formData.get('east');
    //   const west = formData.get('west');
    //   const bboxPolygon = turf.bboxPolygon([parseFloat(west), parseFloat(south), parseFloat(east), parseFloat(north)]);
    //   geoViz.addFeature(bboxPolygon);
    //   formElement.reset();
    // });

    // textPasteForm.addEventListener('submit', (e) => {
    //   e.preventDefault();
    //   hideWizard();

    //   const formElement = document.getElementById('text-paste-form');
    //   const formData = new FormData(formElement);
    //   const coordinatesText = formData.get('coordinates');
    //   const featureType = formData.get('feature-type');
    //   const pattern = /[\s,]+/g;
    //   const coordinateArray = coordinatesText.split(pattern);
    //   // convert coords into array of [lng, lat] pairs
    //   let coordinatePairs = [];
    //   for (let i = 0; i < coordinateArray.length; i += 2) {
    //     const lat = parseFloat(coordinateArray[i]);
    //     const lng = parseFloat(coordinateArray[i + 1]);
    //     if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
    //       coordinatePairs.push([lng, lat]);
    //     }
    //   }

    //   // if featureType is Polygon, add first coordinate pair to the end of the array to close the polygon
    //   if (featureType === 'Polygon') {
    //     coordinatePairs.push(coordinatePairs[0]);
    //     coordinatePairs = [coordinatePairs];
    //   }

    //   const geometry = {
    //     type: featureType,
    //     coordinates: coordinatePairs,
    //   };
    //   const featureCollection = turf.feature(geometry);
    //   geoViz.addFeature(featureCollection);
    //   formElement.reset();
    // });

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
