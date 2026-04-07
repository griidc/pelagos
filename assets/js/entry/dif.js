import '../../scss/dif.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';

import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

import JustValidate from 'just-validate';
import JustValidatePluginDate from 'just-validate-plugin-date';

import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';

// import * as GeoViz from '../modules/geoViz-leaflet';

import GeoViz from '../modules/geoViz';

import * as turf from '@turf/turf';
import { _ } from 'core-js';

const UNSUBMITTED = '0';
const SUBMITTED = '1';
const APPROVED = '2';

document.addEventListener('DOMContentLoaded', () => {

  const geoViz = new GeoViz(document.getElementById('leaflet-map'), {
    // options can be added here in the future if needed
  });

  const form = document.getElementById('difForm');
  const status = document.getElementById('status').value;
  const funders = document.getElementById('funders');
  const fundersSelect = new TomSelect(funders, {
    maxOptions: null,
    plugins: {
      clear_button: {
        title: 'Remove all selected options',
      },
    },
  });

  const researchGroup = document.getElementById('researchGroup');
  const researchGroupSelect = new TomSelect(researchGroup, {
    maxOptions: null,
    plugins: [
      'clear_button',
      'dropdown_input',
    ],
    render: {
      option(data, escape) {
        return `<div locked="${escape(data.locked)}">${escape(data.text)}</div>`;
      },
    },
    // load(query, callback) {
    //   const url = Routing.generate('pelagos_dif_get_research_groups');
    //   fetch(url)
    //     .then((response) => response.json())
    //     .then((json) => {
    //       callback(json.ResearchGroups);
    //       const selectValue = researchGroup.getAttribute('value');
    //       if (selectValue) {
    //         this.setValue(selectValue);
    //         this.disable();
    //       }
    //     }).catch(() => {
    //       callback();
    //     });
    // },
  });

  const formValidate = new JustValidate(form, {
    errorLabelStyle: {
      color: '#b81111',
      fontWeight: 'bold',
    },
  });

  formValidate
    .addField('#researchGroup', [
      {
        rule: 'required',
        errorMessage: 'Project title is required.',
      },
    ])
    .addField('#primaryPointOfContact', [
      {
        rule: 'required',
        errorMessage: 'Primary point of contact is required.',
      },
    ])
    .addField('#funders', [
      {
        rule: 'required',
        errorMessage: 'Funder is required.',
      },
    ])
    .addField('#title', [
      {
        rule: 'required',
        errorMessage: 'Title is required.',
      },
    ])
    .addField('#abstract', [
      {
        rule: 'required',
        errorMessage: 'Abstract is required.',
      },
    ])
    .addRequiredGroup('#dataSize', 'Dataset size is required.',{
      errorsContainer: '#datasize-error',
    })
    .addField('#estimatedStartDate', [
      {
        plugin: JustValidatePluginDate(() => ({
          format: 'yyyy-MM-dd',
          required: true,
        })),
        errorMessage: 'Date is required.',
      },
      {
        plugin: JustValidatePluginDate((fields) => ({
          required: true,
          format: 'yyyy-MM-dd',
          isBefore: fields['#estimatedEndDate'].elem.value,
        })),
        errorMessage: 'Date must be before end date.',
      },
    ])
    .addField('#estimatedEndDate', [
      {
        plugin: JustValidatePluginDate(() => ({
          format: 'yyyy-MM-dd',
          required: true,
        })),
        errorMessage: 'Date is required.',
      },
      {
        plugin: JustValidatePluginDate((fields) => ({
          required: true,
          format: 'yyyy-MM-dd',
          isAfter: fields['#estimatedStartDate'].elem.value,
        })),
        errorMessage: 'Date must be after start date.',
      },
    ])
    .onSuccess((event) => {
      event.currentTarget.submitAction.value = event.submitter.name;
      event.currentTarget.submit();
    })
    ;

  const estimatedStartDate = document.getElementById('estimatedStartDate');
  estimatedStartDate.addEventListener('changeDate', () => {
    if (formValidate.isSubmitted) {
      formValidate.revalidateField('#estimatedStartDate');
      formValidate.revalidateField('#estimatedEndDate');
    }
  });

  const estimatedEndDate = document.getElementById('estimatedEndDate');
  estimatedEndDate.addEventListener('changeDate', () => {
    if (formValidate.isSubmitted) {
      formValidate.revalidateField('#estimatedStartDate');
      formValidate.revalidateField('#estimatedEndDate');
    }
  });

  function populateResearchGroupContacts(contacts) {
    const pointOfContactDropdowns = document.querySelectorAll('.point-of-contact');
    pointOfContactDropdowns.forEach((element) => {
      const dropdown = element;
      const selectedValue = dropdown.getAttribute('data-value');
      dropdown.innerHTML = '';
      const defaultOption = document.createElement('option');
      defaultOption.value = '';
      if (contacts.length === 0) {
        defaultOption.textContent = '[PLEASE SELECT A PROJECT FIRST]';
        defaultOption.disabled = true;
        dropdown.disabled = true;
      } else {
        defaultOption.textContent = '[PLEASE SELECT A CONTACT]';
        dropdown.disabled = false;
      }
      defaultOption.selected = true;
      dropdown.appendChild(defaultOption);
      contacts.forEach((item) => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = `${item.name} (${item.email})`;
        if (item.id === parseInt(selectedValue, 10)) {
          option.selected = true;
        }
        dropdown.appendChild(option);
      });
      if (status !== UNSUBMITTED) {
        dropdown.disabled = true;
      }
    });
  }

  function loadResearchGroupDowndowns(researchGroupId) {
    const url = Routing.generate('pelagos_dif_get_research_group_contacts', { id: researchGroupId });
    let contacts = [];
    fetch(url)
      .then((response) => response.json())
      .then((json) => {
        contacts = json.Contacts;
      })
      .finally(() => {
        populateResearchGroupContacts(contacts);
      });
  }

  researchGroupSelect.on('change', (value) => {
    loadResearchGroupDowndowns(value);
  });

  researchGroupSelect.on('item_add', (value) => {
    const url = Routing.generate('pelagos_dif_check_research_group', { id: value });
    fetch(url)
      .then((response) => response.json())
      .then((json) => {
        if (json.locked) {
          alert('This research group is disabled and cannot be selected.');
        }
      });
  });

  if (researchGroupSelect.getValue() && researchGroupSelect.getValue() !== '') {
    researchGroupSelect.lock();
    loadResearchGroupDowndowns(researchGroupSelect.getValue());
  }

  // on form reset event
  const resetButton = document.getElementById('resetFormButton');
  resetButton.addEventListener('click', () => {
    form.reset(); // reset the form
    // reset tomSelects
    setTimeout(() => {
      if (researchGroupSelect.isLocked === false) {
        researchGroupSelect.clear();
      }

      fundersSelect.clear();
      populateResearchGroupContacts([]);

      // find all form fields
      const formFields = form.querySelectorAll('input, select, textarea');
      formFields.forEach((field) => {
        field.value = "";
        field.removeAttribute('value');
        field.removeAttribute('data-value');
        field.checked = false;
      });
      loadResearchGroupDowndowns(researchGroupSelect.getValue());
      formValidate.clearErrors();
      researchGroup.focus();
    });
  });

  if (status !== UNSUBMITTED) {
    const formFields = form.querySelectorAll('input, select, textarea, button');
    formFields.forEach((field) => {
      researchGroupSelect.disable();
      fundersSelect.disable();
      field.disabled = true;
    });
  }

  geoViz.on('geojsonupdated', (e) => {
    const combinedFeatureCollection = turf.combine(geoViz.getDrawnFeaturesAsGeoJSON());

    let geometryArray = [];
    turf.featureEach(combinedFeatureCollection, function (currentFeature, featureIndex) {
      geometryArray.push(turf.getGeom(currentFeature));
    });
    const geometryCollection = turf.geometryCollection(geometryArray);

    const geometry = combinedFeatureCollection.features[0].geometry;

    const url = Routing.generate('pelagos_app_geojson_to_gml');
    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        geometry: geometry,
      }),
    })
      .then((response) => response.json())
      .then((json) => {
        const gmlOutput = json.gml;
        document.getElementById('spatialExtentGeometry').value = gmlOutput;
      });
  });
});
