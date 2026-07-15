/* eslint-disable import/no-cycle, import/no-extraneous-dependencies,
  import/no-unresolved, import/no-duplicates, import/order,
  import/no-self-import, import/no-relative-packages,
  import/no-named-as-default, import/no-named-as-default-member */
import '../../scss/dataset-submission.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';

import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

import JustValidate from 'just-validate';
import JustValidatePluginDate from 'just-validate-plugin-date';

import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';

import GeoViz from '../modules/geoViz';
import * as turf from '@turf/turf';

document.addEventListener('DOMContentLoaded', () => {
  const geoViz = new GeoViz(document.getElementById('leaflet-map'), {
    loadWizard: true,
  });

  const spatialExtentRadios = document.getElementsByName('has-extent');
  const spatialExtentGeometry = document.getElementsByClassName('spatial-extent-geometry');
  const spatialExtentDescription = document.getElementsByClassName('spatial-extent-description');
  spatialExtentRadios.forEach((radio) => {
    const spatialExtentGeometryField = document.getElementById('spatialExtent');
    const spatialExtentDescriptionField = document.getElementById('spatialExtentDescription');
    const spatialExtentGeometryFieldValue = spatialExtentGeometryField.value ?? '';
    const spatialExtentDescriptionFieldValue = spatialExtentDescriptionField.value ?? '';

    if (spatialExtentDescriptionFieldValue && radio.value === 'no-extent') {
      Array.from(spatialExtentGeometry).forEach((el) => el.classList.add('hidden'));
      Array.from(spatialExtentDescription).forEach((el) => el.classList.remove('hidden'));
      spatialExtentGeometryField.value = '';
      geoViz.clearMap();
      const spatialRadio = radio;
      spatialRadio.checked = true;
    }

    if (spatialExtentGeometryFieldValue && radio.value === 'yes-extent') {
      Array.from(spatialExtentGeometry).forEach((el) => el.classList.remove('hidden'));
      Array.from(spatialExtentDescription).forEach((el) => el.classList.add('hidden'));
      const spatialRadio = radio;
      spatialRadio.checked = true;
      geoViz.fixMapSize();
      const url = Routing.generate('pelagos_app_gml_to_geojson');
      fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          gml: spatialExtentGeometryFieldValue,
        }),
      })
        .then((response) => response.json())
        .then((json) => {
          const geoJSON = JSON.parse(json.geojson);
          const geometry = geoJSON ? geoJSON.geometry : null;
          if (geometry) {
            geoViz.addFeature(geoJSON);
          }
        });
    }

    radio.addEventListener('change', (e) => {
      if (spatialExtentDescriptionFieldValue || spatialExtentGeometryFieldValue) {
        // eslint-disable-next-line no-alert, no-restricted-globals
        if (!confirm('Changing this option will clear any existing information. Do you want to continue?')) {
          e.preventDefault();
          // canceling, so set back to previous selection.
          if (e.target.value === 'yes-extent') {
            document.getElementById('no-extent').checked = true;
          } else if (e.target.value === 'no-extent') {
            document.getElementById('yes-extent').checked = true;
          }
          return;
        }
      }
      if (e.target.value === 'yes-extent') {
        Array.from(spatialExtentGeometry).forEach((el) => el.classList.remove('hidden'));
        Array.from(spatialExtentDescription).forEach((el) => el.classList.add('hidden'));
        spatialExtentDescriptionField.value = '';
        geoViz.fixMapSize();
      } else if (e.target.value === 'no-extent') {
        Array.from(spatialExtentGeometry).forEach((el) => el.classList.add('hidden'));
        Array.from(spatialExtentDescription).forEach((el) => el.classList.remove('hidden'));
        spatialExtentGeometryField.value = '';
        geoViz.clearMap();
      }
    });
  });

  const form = document.getElementById('regForm');
  const datasetContacts = document.getElementsByClassName('contactperson');
  const contactsContainer = document.querySelector('.dataset-contacts');
  const contactTemplate = contactsContainer.querySelector('.dataset-contact');
  const newContactTemplate = contactTemplate.cloneNode(true);
  const contactSelects = [];

  const formValidate = new JustValidate(form, {
    errorLabelStyle: {
      color: '#b81111',
      fontWeight: 'bold',
    },
  });

  const makeContactSelect = (contact) => {
    const contactSelect = new TomSelect(contact, {
      maxOptions: null,
      placeholder: '[Please select a contact.]',
      plugins: {
        clear_button: {
          title: 'Remove all selected options',
        },
      },
    });
    contactSelects.push(contactSelect);
  };

  const addContactButton = document.getElementById('addContactButton');
  addContactButton.addEventListener('click', () => {
    const index = parseInt(contactsContainer.getAttribute('data-index'), 10);

    const existingContacts = contactsContainer.querySelectorAll('.dataset-contact');
    if (existingContacts.length === 1 && !existingContacts[0].querySelector('hr')) {
      const firstHr = document.createElement('hr');
      firstHr.className = 'mt-5 border-gray-300';
      existingContacts[0].appendChild(firstHr);
    }
    const newContact = newContactTemplate.cloneNode(true);
    newContact.innerHTML = newContact.innerHTML.replace(/datasetContacts\[\d+\]/g, `datasetContacts[${index + 1}]`);
    newContact.innerHTML = newContact.innerHTML.replace(/datasetContacts_\d+_/g, `datasetContacts_${index + 1}_`);
    newContact.innerHTML = newContact.innerHTML.replace(/Primary/g, 'Additional');
    const selects = newContact.querySelectorAll('select');
    Array.from(selects).forEach((select) => {
      const selectElement = select;
      selectElement.value = '';
    });
    const primaryContactCheckbox = newContact.querySelector('input[type="checkbox"][id$="_primaryContact"]');
    if (primaryContactCheckbox) {
      primaryContactCheckbox.checked = false;
    }
    const deleteContactButton = newContact.querySelector('.deleteContactButton');
    deleteContactButton.classList.remove('hidden');
    deleteContactButton.addEventListener('click', () => {
      newContact.style.transition = 'opacity 0.3s ease';
      newContact.style.opacity = '0';
      setTimeout(() => {
        contactsContainer.removeChild(newContact);
        const remainingContacts = contactsContainer.querySelectorAll('.dataset-contact');
        if (remainingContacts.length === 1) {
          const hrToRemove = remainingContacts[0].querySelector('hr');
          if (hrToRemove) hrToRemove.remove();
        }
      }, 300);
    });

    const contactPerson = newContact.querySelector('.contactperson');
    const contactRole = newContact.querySelector('.contactrole');
    makeContactSelect(contactPerson);

    formValidate
      .addField(contactPerson, [
        {
          rule: 'required',
          errorMessage: 'Please select a contact.',
        },
      ])
      .addField(contactRole, [
        {
          rule: 'required',
          errorMessage: 'Please select a contact role.',
        },
      ]);

    const currentHr = document.querySelector('div > hr');
    if (!currentHr) {
      const hr = document.createElement('hr');
      hr.className = 'mt-5 border-gray-300';
      newContact.appendChild(hr);
    }

    newContact.style.opacity = '0';
    newContact.style.transition = 'opacity 0.3s ease';
    contactsContainer.appendChild(newContact);
    setTimeout(() => {
      newContact.style.opacity = '1';
    }, 10);
    contactsContainer.setAttribute('data-index', index + 1);
  });

  const deleteContact = document.getElementsByClassName('deleteContactButton');
  Array.from(deleteContact).forEach((button) => {
    button.addEventListener('click', (e) => {
      const contactElement = e.target.closest('.dataset-contact');
      contactElement.style.transition = 'opacity 0.3s ease';
      contactElement.style.opacity = '0';
      setTimeout(() => {
        contactsContainer.removeChild(contactElement);
        const remainingContacts = contactsContainer.querySelectorAll('.dataset-contact');
        if (remainingContacts.length === 1) {
          const hrToRemove = remainingContacts[0].querySelector('hr');
          if (hrToRemove) hrToRemove.remove();
        }
      }, 300);
    });
  });

  Array.from(datasetContacts).forEach((contact) => {
    makeContactSelect(contact);
  });

  const funders = document.getElementById('funders');
  const fundersSelect = new TomSelect(funders, {
    maxOptions: null,
    plugins: {
      clear_button: {
        title: 'Remove all selected options',
      },
    },
  });

  const themeKeywords = document.getElementById('themeKeywords');
  const themeKeywordsSelect = new TomSelect(themeKeywords, {
    plugins: ['remove_button', 'drag_drop'],
    searchField: [],
    render: {
      no_results: null,
    },
    maxOptions: null,
    create: true,
    persist: true,
  });

  const placeKeywords = document.getElementById('placeKeywords');
  const placeKeywordsSelect = new TomSelect(placeKeywords, {
    plugins: ['remove_button', 'drag_drop'],
    searchField: [],
    render: {
      no_results: null,
    },
    maxOptions: null,
    create: true,
    persist: true,
  });

  const topicKeywords = document.getElementById('topic-keyword-select');
  const topicKeywordsSelect = new TomSelect(topicKeywords, {
    plugins: ['remove_button'],
    maxOptions: null,
    create: false,
    persist: false,
    render: {
      option(data, escape) {
        return `<div class="topic-keyword-option">
          <span class="topic-keyword-option-text">${escape(data.text)}</span>
          <span class="topic-keyword-option-description">${escape(data.description)}</span>
        </div>`;
      },
    },
  });

  // Prevent form submission on Enter key press for all fields except buttons
  form.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && event.target.tagName !== 'BUTTON' && !event.target.classList.contains('button')) {
      event.preventDefault();
    }
  });

  const contactPersons = document.querySelectorAll('select.contactperson');
  contactPersons.forEach((contactPerson) => {
    formValidate.addField(contactPerson, [
      {
        rule: 'required',
        errorMessage: 'Please select a contact.',
      },
    ]);
  });

  const contactRoles = document.querySelectorAll('select.contactrole');
  contactRoles.forEach((contactRole) => {
    formValidate.addField(contactRole, [
      {
        rule: 'required',
        errorMessage: 'Please select a contact role.',
      },
    ]);
  });

  formValidate
    .addField('#authors', [
      {
        rule: 'required',
        errorMessage: 'Please select at least one author.',
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
    .addField('#purpose', [
      {
        rule: 'required',
        errorMessage: 'Purpose is required.',
      },
    ])
    .addField('#suppParams', [
      {
        rule: 'required',
        errorMessage: 'Data parameters and units are required.',
      },
    ])
    .addField('#themeKeywords', [
      {
        rule: 'required',
        errorMessage: 'Theme keywords are required.',
      },
    ])
    .addField('#topic-keyword-select', [
      {
        rule: 'required',
        errorMessage: 'Please select at least one topic category keyword.',
      },
    ])
    .addField('#temporalExtentDesc', [
      {
        validator: (value, context) => {
          const temporalExtentDesc = context['#temporalExtentDesc'].elem;
          if (temporalExtentDesc.checkVisibility() && !temporalExtentDesc.value.trim()) {
            return false;
          }
          return true;
        },
        errorMessage: 'Temporal extent description is required.',
      },
    ])
    .addField('#spatial-extent', [
      {
        validator: () => {
          const spatialExtentGeometryElement = document.getElementById('spatialExtent');
          const spatialExtentDescriptionElement = document.getElementById('spatialExtentDescription');
          // Check if either the description or geometry field is filled
          if (spatialExtentDescriptionElement.value.trim()) {
            return true;
          }
          if (spatialExtentGeometryElement.value.trim()) {
            return true;
          }
          return false;
        },
        errorMessage: 'Please provide either a spatial extent geometry or a spatial extent description.',
      },
    ], {
      errorsContainer: '#spatial-extent-error',
    })
    .addField('#temporalExtentBeginPosition', [
      {
        plugin: JustValidatePluginDate(() => ({
          format: 'yyyy-MM-dd',
          required: true,
        })),
        errorMessage: 'Date is required.',
      },
      // {
      //   plugin: JustValidatePluginDate((fields) => ({
      //     required: true,
      //     format: 'yyyy-MM-dd',
      //     isBefore: fields['#temporalExtentEndPosition'].elem.value,
      //   })),
      //   errorMessage: 'Date must be before end date.',
      // },
    ])
    .addField('#temporalExtentEndPosition', [
      {
        plugin: JustValidatePluginDate(() => ({
          format: 'yyyy-MM-dd',
          required: true,
        })),
        errorMessage: 'Date is required.',
      },
      // {
      //   plugin: JustValidatePluginDate((fields) => ({
      //     required: true,
      //     format: 'yyyy-MM-dd',
      //     isAfter: fields['#temporalExtentBeginPosition'].elem.value,
      //   })),
      //   errorMessage: 'Date must be after start date.',
      // },
    ])
    .onSuccess((event) => {
      const successEvent = event;
      successEvent.currentTarget.submitAction.value = event.submitter.name;
      successEvent.currentTarget.submit();
    });

  const spatialExtentDescriptionElement = document.getElementById('spatialExtentDescription');
  spatialExtentDescriptionElement.addEventListener('change', () => {
    if (formValidate.isSubmitted) {
      formValidate.revalidateField('#spatial-extent');
    }
  });

  const estimatedStartDate = document.getElementById('temporalExtentBeginPosition');
  estimatedStartDate.addEventListener('changeDate', () => {
    if (formValidate.isSubmitted) {
      formValidate.revalidateField('#temporalExtentBeginPosition');
      formValidate.revalidateField('#temporalExtentEndPosition');
    }
  });

  const estimatedEndDate = document.getElementById('temporalExtentEndPosition');
  estimatedEndDate.addEventListener('changeDate', () => {
    if (formValidate.isSubmitted) {
      formValidate.revalidateField('#temporalExtentBeginPosition');
      formValidate.revalidateField('#temporalExtentEndPosition');
    }
  });

  geoViz.on('geojsonupdated', (e) => {
    const geometryType = e.geojson ? turf.getType(e.geojson) : '';
    const spatialExtent = document.getElementById('spatialExtent');
    let geometry = null;
    if (geometryType === 'Point') {
      const geoJSON = geoViz.getDrawnFeaturesAsGeoJSON();
      const combinedFeature = turf.combine(geoJSON);
      geometry = combinedFeature.features.length > 0 ? combinedFeature.features[0].geometry : null;

      if (!geometry) {
        spatialExtent.value = '';
        return;
      }
    } else {
      geometry = e.geojson ? e.geojson.geometry : '';
      if (!geometry || e.removed === true) {
        spatialExtent.value = '';
        return;
      }
    }

    const url = Routing.generate('pelagos_app_geojson_to_gml');
    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        geometry,
      }),
    })
      .then((response) => response.json())
      .then((json) => {
        const gmlOutput = json.gml;
        console.warn('GML Output:', gmlOutput);
        spatialExtent.value = gmlOutput;
        if (formValidate.isSubmitted) {
          formValidate.revalidateField('#spatial-extent');
        }
      });
  });

  // on form reset event
  // const resetButton = document.getElementById('resetFormButton');
  // resetButton.addEventListener('click', () => {
  //   form.reset(); // reset the form
  //   // reset tomSelects
  //   setTimeout(() => {
  //     fundersSelect.clear();
  //     contactSelects.forEach((contactSelect) => contactSelect.clear());
  //     themeKeywordsSelect.clear();
  //     placeKeywordsSelect.clear();
  //     topicKeywordsSelect.clear();

  //     // find all form fields
  //     const formFields = form.querySelectorAll('input:not([helper]), select, textarea');
  //     formFields.forEach((field) => {
  //       const formField = field;
  //       formField.value = '';
  //       formField.removeAttribute('value');
  //       formField.removeAttribute('data-value');
  //       formField.checked = false;
  //     });
  //     Array.from(spatialExtentDescription).forEach((el) => el.classList.add('hidden'));
  //     Array.from(spatialExtentGeometry).forEach((el) => el.classList.add('hidden'));
  //     formValidate.refresh();
  //   });
  // });
});
