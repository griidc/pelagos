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

import '../file-manager';

const DATASET_SUBMISSION_STATES = {
  STATUS_UNSUBMITTED: '0',
  STATUS_INCOMPLETE: '1',
  STATUS_COMPLETE: '2',
  STATUS_IN_REVIEW: '3',
};

// get query string, if it has paramater regid, then change it to udi.
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('regid')) {
  const regidValue = urlParams.get('regid');
  urlParams.delete('regid');
  urlParams.set('udi', regidValue);
  const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
  window.history.replaceState({}, '', newUrl);
}

document.addEventListener('DOMContentLoaded', () => {
  const geoViz = new GeoViz(document.getElementById('leaflet-map'), {
    loadWizard: true,
  });

  const form = document.getElementById('regForm');
  const status = document.getElementById('status').value;
  const isDrpm = document.getElementById('isDrpm')?.value === '1';
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

  const spatialExtentRadios = document.getElementsByName('has-extent');
  const spatialExtentGeometry = document.getElementsByClassName('spatial-extent-geometry');
  const spatialExtentDescription = document.getElementsByClassName('spatial-extent-description');
  spatialExtentRadios.forEach((radio) => {
    const spatialExtentGeometryField = document.getElementById('spatialExtent');
    const spatialExtentDescriptionField = document.getElementById('spatialExtentDescription');
    const temporalExtentDescriptionField = document.getElementById('temporalExtentDesc');
    const temporalExtentBeginPositionField = document.getElementById('temporalExtentBeginPosition');
    const temporalExtentEndPositionField = document.getElementById('temporalExtentEndPosition');

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
        const temporalExtentDescriptionTomSelect = temporalExtentDescriptionField.tomselect;
        if (temporalExtentDescriptionTomSelect) {
          temporalExtentDescriptionTomSelect.clear();
        }
        temporalExtentDescriptionField.value = '';
        temporalExtentDescriptionField.selectedIndex = 0;
        temporalExtentBeginPositionField.value = '';
        temporalExtentEndPositionField.value = '';
        spatialExtentGeometryField.value = '';

        geoViz.clearMap();
      }

      formValidate.revalidateField('#has-extent');
    });
  });

  const filesSectionRadios = document.getElementsByName('has-files');
  const fileSection = document.getElementsByClassName('files-section');
  const remoteSection = document.getElementsByClassName('remotely-hosted-section');
  filesSectionRadios.forEach((radio) => {
    const filesUploaded = document.getElementById('filesUploaded');
    const remoteHostedUrl = document.getElementById('remotelyHostedUrl');
    const filesUploadedCount = filesUploaded.value ?? '';
    const remoteHostedUrlValue = remoteHostedUrl.value ?? '';

    if (remoteHostedUrlValue && radio.value === 'no-files') {
      Array.from(fileSection).forEach((el) => el.classList.remove('hidden'));
      Array.from(remoteSection).forEach((el) => el.classList.add('hidden'));
      filesUploaded.value = '';
      const filesRadio = radio;
      filesRadio.checked = true;
    }

    if (filesUploadedCount && radio.value === 'yes-files') {
      Array.from(fileSection).forEach((el) => el.classList.remove('hidden'));
      Array.from(remoteSection).forEach((el) => el.classList.add('hidden'));
      const filesRadio = radio;
      filesRadio.checked = true;
    }

    radio.addEventListener('change', (e) => {
      if (e.target.value === 'yes-files') {
        Array.from(fileSection).forEach((el) => el.classList.remove('hidden'));
        Array.from(remoteSection).forEach((el) => el.classList.add('hidden'));
        // filesUploaded.value = '';
      } else if (e.target.value === 'no-files') {
        Array.from(fileSection).forEach((el) => el.classList.add('hidden'));
        Array.from(remoteSection).forEach((el) => el.classList.remove('hidden'));
        // remoteHostedUrl.value = '';
      }

      formValidate.revalidateField('#has-files');
    });
  });

  const makeContactSelect = (contact) => {
    const contactSelect = new TomSelect(contact, {
      maxOptions: null,
      placeholder: 'Please select a contact.',
      closeAfterSelect: true,
      hidePlaceholder: true,
      render: {
        option(data, escape) {
          return `<div>
              ${escape(data.lastname)}, ${escape(data.firstname)} (${escape(data.email)})
          </div>`;
        },
        item(data, escape) {
          return `<div>${escape(data.lastname)}, ${escape(data.firstname)}</div>`;
        },
      },
      plugins: {
        clear_button: {
          title: 'Remove all selected options',
        },
      },
    });

    const role = contact.closest('.dataset-contact').querySelector('.contactrole');
    const roleSelect = new TomSelect(role, {
      hidePlaceholder: true,
      closeAfterSelect: true,
      placeholder: ' Please select a role.',
    });

    contactSelects.push({ contactSelect, roleSelect });
  };

  const addContactButton = document.getElementById('addContactButton');
  addContactButton.addEventListener('click', () => {
    const index = parseInt(contactsContainer.getAttribute('data-index'), 10);
    const newContact = newContactTemplate.cloneNode(true);
    newContact.innerHTML = newContact.innerHTML.replace(/datasetContacts\[\d+\]/g, `datasetContacts[${index + 1}]`);
    newContact.innerHTML = newContact.innerHTML.replace(/datasetContacts_\d+_/g, `datasetContacts_${index + 1}_`);
    newContact.innerHTML = newContact.innerHTML.replace(/Primary/g, 'Additional');
    const hr = newContact.querySelector('.top-hr > hr');
    hr.classList.remove('hidden');

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
    closeAfterSelect: true,
    hidePlaceholder: true,
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
    closeAfterSelect: true,
    hidePlaceholder: true,
    placeholder:
      'Please provide commonly used words or short phrases that describe themes or subjects that describe the dataset.',
  });

  themeKeywordsSelect.inputState();

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
    closeAfterSelect: true,
    hidePlaceholder: true,
    placeholder:
      'Please provide commonly used words or short phrases that describe the geographic areas that describe the dataset.',
  });

  placeKeywordsSelect.inputState();

  const topicKeywords = document.getElementById('topic-keyword-select');
  const topicKeywordsSelect = new TomSelect(topicKeywords, {
    plugins: ['remove_button'],
    maxOptions: null,
    closeAfterSelect: true,
    hidePlaceholder: true,
    create: false,
    persist: false,
    placeholder: 'Please provide broad theme keywords pre-defined by the ISO 19115-2 metadata standard used by GRIIDC.',
    render: {
      option(data, escape) {
        return `<div class="topic-keyword-option">
          <span class="topic-keyword-option-text">${escape(data.text)}</span>
          <span class="topic-keyword-option-description">${escape(data.description)}</span>
        </div>`;
      },
    },
  });
  topicKeywordsSelect.inputState();

  const temporalExtentDesc = document.getElementById('temporalExtentDesc');
  const temporalExtentDescSelect = new TomSelect(temporalExtentDesc, {
    searchField: [],
    create: false,
    persist: false,
    maxItems: 1,
    closeAfterSelect: true,
    hidePlaceholder: true,
    placeholder: 'Please select a description of what the time period represents.',
  });
  temporalExtentDescSelect.inputState();

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
          const temporalExtentDescElement = context['#temporalExtentDesc'].elem;
          if (temporalExtentDescElement.checkVisibility() && !temporalExtentDescElement.value.trim()) {
            return false;
          }
          return true;
        },
        errorMessage: 'Temporal extent description is required.',
      },
    ])
    .addField('#has-extent', [
      {
        validator: () => {
          const selectedRadio = Array.from(spatialExtentRadios).find((radio) => radio.checked);
          if (selectedRadio) {
            return true;
          }
          const spatialExentSection = document.getElementById('extent');
          setTimeout(() => spatialExentSection.scrollIntoView(true), 0);
          return false;
        },
        errorMessage: 'Please select if the dataset has a spatial extent geometry or a spatial extent description.',
      },
    ], {
      errorsContainer: '#has-extent-error',
    })
    .addField('#temporalExtentBeginPosition', [
      {
        validator: (value, context) => {
          const temporalExtentBeginPosition = context['#temporalExtentBeginPosition'].elem;
          if (temporalExtentBeginPosition.checkVisibility() && !temporalExtentBeginPosition.value.trim()) {
            return false;
          }
          return true;
        },
        errorMessage: 'Date is required.',
      },
      {
        plugin: JustValidatePluginDate(() => ({
          format: 'yyyy-MM-dd',
        })),
        errorMessage: 'Date must be in the format yyyy-MM-dd.',
      },
      {
        plugin: JustValidatePluginDate((fields) => ({
          format: 'yyyy-MM-dd',
          isBefore: fields['#temporalExtentEndPosition'].elem.value,
        })),
        errorMessage: 'Date must be before end date.',
      },
    ])
    .addField('#temporalExtentEndPosition', [
      {
        validator: (value, context) => {
          const temporalExtentEndPosition = context['#temporalExtentEndPosition'].elem;
          if (temporalExtentEndPosition.checkVisibility() && !temporalExtentEndPosition.value.trim()) {
            return false;
          }
          return true;
        },
        errorMessage: 'Date is required.',
      },
      {
        plugin: JustValidatePluginDate(() => ({
          format: 'yyyy-MM-dd',
        })),
        errorMessage: 'Date must be in the format yyyy-MM-dd.',
      },
      {
        plugin: JustValidatePluginDate((fields) => ({
          format: 'yyyy-MM-dd',
          isAfter: fields['#temporalExtentBeginPosition'].elem.value,
        })),
        errorMessage: 'Date must be after start date.',
      },
    ])
    .addField('#spatial-extent', [
      {
        validator: () => {
          const selectedRadio = Array.from(spatialExtentRadios).find((radio) => radio.checked);
          if (!selectedRadio) {
            return true;
          }
          const spatialExtentGeometryElement = document.getElementById('spatialExtent');
          const spatialExtentDescriptionElement = document.getElementById('spatialExtentDescription');
          // Check if either the description or geometry field is filled
          if (spatialExtentDescriptionElement.value.trim()) {
            return true;
          }
          if (spatialExtentGeometryElement.value.trim()) {
            return true;
          }

          if (!spatialExtentDescriptionElement.value.trim() && spatialExtentDescriptionElement.checkVisibility()) {
            setTimeout(() => spatialExtentDescriptionElement.focus(), 0);
          } else {
            const spatialExentSection = document.getElementById('extent');
            setTimeout(() => spatialExentSection.scrollIntoView(true), 0);
          }
          return false;
        },
        errorMessage: 'Please provide either a spatial extent geometry or a spatial extent description.',
      },
    ], {
      errorsContainer: '#spatial-extent-error',
    })
    .addField('#has-files', [
      {
        validator: () => {
          const selectedRadio = Array.from(filesSectionRadios).find((radio) => radio.checked);
          if (selectedRadio) {
            return true;
          }
          const hasFilesSection = document.getElementById('submit');
          setTimeout(() => hasFilesSection.scrollIntoView(true), 0);
          return false;
        },
        errorMessage: 'Please select if the dataset has files or is remotely hosted.',
      },
    ], {
      errorsContainer: '#has-files-error',
    })
    .addField('#filesUploaded', [
      {
        validator: () => {
          const selectedRadio = Array.from(filesSectionRadios).find((radio) => radio.checked);
          if (!selectedRadio) {
            return true;
          }
          const filesUploadedElement = document.getElementById('filesUploaded');
          const remotelyHostedUrlElement = document.getElementById('remotelyHostedUrl');
          // Check if either the file section or remote section is filled
          if (filesUploadedElement.value === 'valid') {
            return true;
          }
          if (remotelyHostedUrlElement.value.trim()) {
            return true;
          }

          if (!filesUploadedElement.value.trim() && remotelyHostedUrlElement.checkVisibility()) {
            setTimeout(() => remotelyHostedUrlElement.focus(), 0);
          } else {
            const submitSection = document.getElementById('submit');
            setTimeout(() => submitSection.scrollIntoView(true), 0);
          }
          return false;
        },
        errorMessage: 'Please provide files or a remotely hosted URL.',
      },
    ], {
      errorsContainer: '#files-uploaded-error',
    })
    .addField('#remotelyHostedUrl', [
      {
        validator: (value) => {
          const remotelyHostedUrlElement = document.getElementById('remotelyHostedUrl');
          if (value.trim() && remotelyHostedUrlElement.checkVisibility()) {
            return true;
          }

          if (!value.trim() && remotelyHostedUrlElement.checkVisibility()) {
            return false;
          }

          // Element is not visible, so we don't need to validate it
          return true;
        },
        errorMessage: 'Please provide a remotely hosted URL.',
      },
      {
        validator: (value) => {
          if (!value.trim()) {
            return true;
          }
          if (URL.canParse(value)) {
            return true;
          }
          return false;
        },
        errorMessage: 'Please enter a valid URL.',
      },
    ])
    .onSuccess((event) => {
      const successEvent = event;
      successEvent.currentTarget.submitAction.value = event.submitter.name;
      successEvent.currentTarget.submit();
    });

  const spatialExtentDescriptionElement = document.getElementById('spatialExtentDescription');
  spatialExtentDescriptionElement.addEventListener('input', () => {
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

  const spatialExtentSelector = document.getElementsByName('has-extent');
  Array.from(spatialExtentSelector).forEach((radio) => {
    radio.addEventListener('change', () => {
      if (formValidate.isSubmitted) {
        formValidate.revalidateField('#has-extent');
      }
    });
  });

  const filesUploadedSelector = document.getElementById('filesUploaded');
  filesUploadedSelector.addEventListener('change', () => {
    if (formValidate.isSubmitted) {
      formValidate.revalidateField('#filesUploaded');
    }
  });

  const remotelyHostedUrlSelector = document.getElementById('remotelyHostedUrl');
  remotelyHostedUrlSelector.addEventListener('input', () => {
    if (formValidate.isSubmitted) {
      formValidate.revalidateField('#remotelyHostedUrl');
    }
  });

  if (status !== DATASET_SUBMISSION_STATES.STATUS_INCOMPLETE && !isDrpm) {
    const formFields = form.querySelectorAll('input, select, textarea, button');
    formFields.forEach((field) => {
      const formField = field;
      formField.disabled = true;
    });
    const tomSelectInstances = Array.from(
      document.querySelectorAll('.tomselected, .ts-wrapper + select, .ts-wrapper + input'),
    )
      .map((element) => element.tomselect)
      .filter((instance) => instance !== undefined);
    tomSelectInstances.forEach((instance) => instance.disable());
  }

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

  const saveButon = document.getElementById('saveAndContinue');
  saveButon.addEventListener('click', () => {
    formValidate.destroy();
    form.submitAction.value = 'saveAndContinue';
    form.submit();
  });

  // on form reset event
  const resetButton = document.getElementById('resetFormButton');
  if (resetButton) {
    resetButton.addEventListener('click', () => {
      form.reset(); // reset the form
      // reset tomSelects
      setTimeout(() => {
        fundersSelect.clear();
        contactSelects.forEach((select) => {
          select.contactSelect.clear();
          select.roleSelect.clear();
        });
        themeKeywordsSelect.clear();
        placeKeywordsSelect.clear();
        topicKeywordsSelect.clear();
        temporalExtentDescSelect.clear();

        // find all form fields
        const formFields = form.querySelectorAll('input:not([helper]), select, textarea');
        formFields.forEach((field) => {
          const formField = field;
          formField.value = '';
          formField.removeAttribute('value');
          formField.removeAttribute('data-value');
          formField.checked = false;
        });
        Array.from(spatialExtentDescription).forEach((el) => el.classList.add('hidden'));
        Array.from(spatialExtentGeometry).forEach((el) => el.classList.add('hidden'));
        formValidate.refresh();
      });
    });
  }

  const mainSection = document.getElementById('mainsection');
  mainSection.classList.remove('loading');
});
