/* eslint-disable import/no-cycle, import/no-extraneous-dependencies,
  import/no-unresolved, import/no-duplicates, import/order,
  import/no-self-import, import/no-relative-packages,
  import/no-named-as-default, import/no-named-as-default-member */
import '../../scss/dataset-submission.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';

import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

import JustValidate from 'just-validate';
// import JustValidatePluginDate from 'just-validate-plugin-date';

import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';

import GeoViz from '../modules/geoViz';

document.addEventListener('DOMContentLoaded', () => {
  const geoViz = new GeoViz(document.getElementById('leaflet-map'), {
    loadWizard: true,
  });

  const spatialExtentRadios = document.getElementsByName('has-extent');
  const spatialExtentGeometry = document.getElementById('spatial-extent-geometry');
  const spatialExtentDescription = document.getElementById('spatial-extent-description');
  spatialExtentRadios.forEach((radio) => {
    const spatialExtentGeometryField = document.getElementById('spatialExtent');
    const spatialExtentDescriptionField = document.getElementById('spatialExtentDescription');
    const spatialExtentGeometryFieldValue = spatialExtentGeometryField.value ?? '';
    const spatialExtentDescriptionFieldValue = spatialExtentDescriptionField.value ?? '';

    if (spatialExtentDescriptionFieldValue && radio.value === 'no-extent') {
      spatialExtentGeometry.classList.add('hidden');
      spatialExtentDescription.classList.remove('hidden');
      spatialExtentGeometryField.value = '';
      geoViz.clearMap();
      const spatialRadio = radio;
      spatialRadio.checked = true;
    }

    if (spatialExtentGeometryFieldValue && radio.value === 'yes-extent') {
      spatialExtentGeometry.classList.remove('hidden');
      spatialExtentDescription.classList.add('hidden');
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
        spatialExtentGeometry.classList.remove('hidden');
        spatialExtentDescription.classList.add('hidden');
        spatialExtentDescriptionField.value = '';
        geoViz.fixMapSize();
      } else if (e.target.value === 'no-extent') {
        spatialExtentGeometry.classList.add('hidden');
        spatialExtentDescription.classList.remove('hidden');
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
    const newContact = newContactTemplate.cloneNode(true);
    newContact.innerHTML = newContact.innerHTML.replace(/datasetContacts\[\d+\]/g, `datasetContacts[${index + 1}]`);
    newContact.innerHTML = newContact.innerHTML.replace(/datasetContacts_\d+_/g, `datasetContacts_${index + 1}_`);
    newContact.innerHTML = newContact.innerHTML.replace(/Primary/g, 'Additional');
    const selects = newContact.querySelectorAll('select');
    Array.from(selects).forEach((select) => {
      const selectElement = select;
      selectElement.value = '';
    });
    const deleteContactButton = newContact.querySelector('.deleteContactButton');
    deleteContactButton.classList.remove('hidden');
    deleteContactButton.addEventListener('click', () => {
      newContact.style.transition = 'opacity 0.3s ease';
      newContact.style.opacity = '0';
      setTimeout(() => {
        contactsContainer.removeChild(newContact);
      }, 300);
    });

    const contact = newContact.querySelector('.contactperson');
    makeContactSelect(contact);

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
  });

  // Prevent form submission on Enter key press for all fields except buttons
  form.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && event.target.tagName !== 'BUTTON' && !event.target.classList.contains('button')) {
      event.preventDefault();
    }
  });

  const formValidate = new JustValidate(form, {
    errorLabelStyle: {
      color: '#b81111',
      fontWeight: 'bold',
    },
  });

  formValidate
    .addField('.contactperson', [
      {
        rule: 'required',
        errorMessage: 'Please select a contact.',
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
    .addField('#themeKeywords', [
      {
        rule: 'required',
        errorMessage: 'Theme keywords are required.',
      },
    ])
    // .addField('#estimatedStartDate', [
    //   {
    //     plugin: JustValidatePluginDate(() => ({
    //       format: 'yyyy-MM-dd',
    //       required: true,
    //     })),
    //     errorMessage: 'Date is required.',
    //   },
    //   {
    //     plugin: JustValidatePluginDate((fields) => ({
    //       required: true,
    //       format: 'yyyy-MM-dd',
    //       isBefore: fields['#estimatedEndDate'].elem.value,
    //     })),
    //     errorMessage: 'Date must be before end date.',
    //   },
    // ])
    // .addField('#estimatedEndDate', [
    //   {
    //     plugin: JustValidatePluginDate(() => ({
    //       format: 'yyyy-MM-dd',
    //       required: true,
    //     })),
    //     errorMessage: 'Date is required.',
    //   },
    //   {
    //     plugin: JustValidatePluginDate((fields) => ({
    //       required: true,
    //       format: 'yyyy-MM-dd',
    //       isAfter: fields['#estimatedStartDate'].elem.value,
    //     })),
    //     errorMessage: 'Date must be after start date.',
    //   },
    // ])
    .onSuccess((event) => {
      const successEvent = event;
      successEvent.currentTarget.submitAction.value = event.submitter.name;
      successEvent.currentTarget.submit();
    });

  // on form reset event
  const resetButton = document.getElementById('resetFormButton');
  resetButton.addEventListener('click', () => {
    form.reset(); // reset the form
    // reset tomSelects
    setTimeout(() => {
      // if (researchGroupSelect.isLocked === false) {
      //   researchGroupSelect.clear();
      // }

      fundersSelect.clear();
      contactSelects.forEach((contactSelect) => contactSelect.clear());
      themeKeywordsSelect.clear();
      placeKeywordsSelect.clear();
      topicKeywordsSelect.clear();
      // populateResearchGroupContacts([]);

      // find all form fields
      const formFields = form.querySelectorAll('input:not([helper]), select, textarea');
      formFields.forEach((field) => {
        const formField = field;
        formField.value = '';
        formField.removeAttribute('value');
        formField.removeAttribute('data-value');
        formField.checked = false;
      });
      spatialExtentDescription.classList.add('hidden');
      spatialExtentGeometry.classList.add('hidden');
      // loadResearchGroupDowndowns(researchGroupSelect.getValue());
      formValidate.clearErrors();
      // researchGroup.focus();
    });
  });
});
