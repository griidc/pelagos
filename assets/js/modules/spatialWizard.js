import { Modal } from 'flowbite';
import * as turf from '@turf/turf';
import JustValidate from 'just-validate';

import modalTemplate from './templates/spatialWizard.html';

let map = null;
let geoViz = null;

const COORDINATE_ORDER = {
  LATLng: 'latLng',
  LONGlat: 'lngLat',
};

const spatialWizardHideEvent = new CustomEvent('spatialWizardHide');

const coordinateListToPairsArray = (text, order = COORDINATE_ORDER.LONGlat) => {
  const pattern = /[\s,]+/g;
  const list = text.split(pattern);
  const coordinatePairs = [];
  for (let i = 0; i < list.length; i += 2) {
    const lng = parseFloat(list[i]);
    const lat = parseFloat(list[i + 1]);
    if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
      if (order === COORDINATE_ORDER.LATLng) {
        coordinatePairs.push([lat, lng]);
      } else {
        coordinatePairs.push([lng, lat]);
      }
    }
  }
  return coordinatePairs;
};

export default class SpatialWizard {
  constructor(geoVizInstance) {
    geoViz = geoVizInstance;
    map = geoVizInstance.map;
    this.modalInstance = null;

    const newElement = document.createElement('div');
    newElement.innerHTML = modalTemplate;
    const modalElement = newElement.firstChild;
    document.body.appendChild(modalElement);

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
        window.dispatchEvent(spatialWizardHideEvent);
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
        window.dispatchEvent(spatialWizardHideEvent);
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
        {
          validator: (value) => {
            const list = coordinateListToPairsArray(value);

            for (let i = 0; i < list.length; i += 1) {
              const [lng, lat] = list[i];
              if (lat < -90 || lat > 90) {
                return false;
              }
              if (lng < -180 || lng > 180) {
                return false;
              }
            }
            return true;
          },
          errorMessage: 'Coordinates must be between -90 and 90 for latitude and -180 and 180 for longitude.',
        },
        {
          validator: (value) => {
            const list = coordinateListToPairsArray(value);
            const featureType = textPasteForm.querySelector('input[name="feature-type"]:checked').value;

            if (featureType === 'Polygon' && list.length < 3) {
              return false;
            }

            if (featureType === 'LineString' && list.length < 2) {
              return false;
            }

            return true;
          },
          errorMessage: 'Please provide at least 3 coordinate pairs for a Polygon.',
        },
        {
          validator: (value) => {
            const list = coordinateListToPairsArray(value);
            const featureType = textPasteForm.querySelector('input[name="feature-type"]:checked').value;

            if (featureType === 'LineString' && list.length < 2) {
              return false;
            }

            return true;
          },
          errorMessage: 'Please provide at least 2 coordinate pairs for a LineString.',
        },
      ])
      .addRequiredGroup('#feature-type', 'Feature type is required.', {
        errorsContainer: '#feature-type-error',
      })
      .onSuccess((event) => {
        window.dispatchEvent(spatialWizardHideEvent);

        const formElement = event.srcElement;
        const formData = new FormData(formElement);
        const coordinatesText = formData.get('coordinates');
        const featureType = formData.get('feature-type');
        let coordinatePairs = coordinateListToPairsArray(coordinatesText);

        // if featureType is Polygon, add first coordinate pair to the end of the array to close the polygon
        if (featureType === 'Polygon') {
          if (coordinatePairs[0] !== coordinatePairs[coordinatePairs.length - 1]) {
            coordinatePairs.push(coordinatePairs[0]);
          }
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

    const modalInstance = new Modal(
      modalElement,
      {
        backdrop: 'static',
        placement: 'center',
        closable: false,
        backdropClasses: 'z-[9998] bg-gray-900/70 fixed inset-0',
        onHide: () => {
          textPasteFormValidate.refresh();
          pointFormValidate.refresh();
          bboxFormValidate.refresh();
        },
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

      const json = geoViz.getDrawnFeaturesAsGeoJSON();
      if (!json || !json.features || json.features.length === 0) {
        return;
      }

      const combined = turf.combine(json);
      const exploded = turf.explode(combined);
      const featureType = turf.getType(combined.features[0]);

      let coordinatesText = '';
      for (let i = 0; i < exploded.features.length; i += 1) {
        const coords = turf.getCoords(exploded.features[i]);
        const [lng, lat] = coords;
        coordinatesText += `${lng}, ${lat}\n`;
      }

      const coordinatesTextArea = modalElement.querySelector('#coordinates');
      const featureTypeMultiPoint = modalElement.querySelector('#multipoint');
      const featureTypePolygon = modalElement.querySelector('#polygon');
      const featureTypeLineString = modalElement.querySelector('#linestring');

      switch (featureType) {
        case 'MultiPoint':
          featureTypeMultiPoint.checked = true;
          break;
        case 'MultiPolygon':
          featureTypePolygon.checked = true;
          break;
        case 'MultiLineString':
          featureTypeLineString.checked = true;
          break;
        default:
          break;
      }

      coordinatesTextArea.value = coordinatesText.trim();
    };

    const hideWizard = () => {
      modalInstance.hide();
    };

    modalElement.querySelector('#modal-close-button').addEventListener('click', () => {
      hideWizard();
    });

    window.addEventListener('spatialWizardHide', () => hideWizard());

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
