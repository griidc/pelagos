/* eslint-disable import/no-cycle, import/no-extraneous-dependencies,
  import/no-unresolved, import/no-duplicates, import/order,
  import/no-self-import, import/no-relative-packages,
  import/no-named-as-default, import/no-named-as-default-member */
import '../../scss/dif.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';

import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

import JustValidate from 'just-validate';
import JustValidatePluginDate from 'just-validate-plugin-date';

import GeoViz from '../modules/geoViz';
import FullScreenModal from '../modules/fullScreenModal';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';

const DIF_STATES = {
  UNSUBMITTED: '0',
  SUBMITTED: '1',
  APPROVED: '2',
};

document.addEventListener('DOMContentLoaded', () => {
  const geoViz = new GeoViz(document.getElementById('leaflet-map'), {
    loadWizard: true,
  });

  const spatialExtentRadios = document.getElementsByName('has-extent');
  const spatialExtentGeometry = document.getElementById('spatial-extent-geometry');
  const spatialExtentDescription = document.getElementById('spatial-extent-description');
  spatialExtentRadios.forEach((radio) => {
    const spatialExtentGeometryField = document.getElementById('spatialExtentGeometry');
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

  const form = document.getElementById('difForm');
  const status = document.getElementById('status').value;
  const isDrpm = document.getElementById('isDrpm')?.value === '1';
  const funders = document.getElementById('funders');
  const fundersSelect = new TomSelect(funders, {
    maxOptions: null,
    hidePlaceholder: true,
    plugins: {
      remove_button: {
        title: 'Remove this funder',
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
    .addRequiredGroup('#dataSize', 'Dataset size is required.', {
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
      const successEvent = event;
      successEvent.currentTarget.submitAction.value = event.submitter.name;
      successEvent.currentTarget.submit();
    });

  const saveAndContinueButton = document.getElementById('saveAndContinue');
  saveAndContinueButton.addEventListener('click', (event) => {
    event.preventDefault();
    form.submitAction.value = 'saveAndContinue';
    form.submit();
  });

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
        defaultOption.textContent = '[Please select a project first.]';
        defaultOption.disabled = true;
        dropdown.disabled = true;
      } else {
        defaultOption.textContent = '[Please select a contact.]';
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
      if (status !== DIF_STATES.UNSUBMITTED && !isDrpm) {
        dropdown.disabled = true;
      }
    });
  }

  function loadResearchGroupDowndowns(researchGroupId) {
    if (!researchGroupId) {
      populateResearchGroupContacts([]);
      return;
    }
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

  if (researchGroupSelect.getValue() && researchGroupSelect.getValue() !== '') {
    researchGroupSelect.lock();
    loadResearchGroupDowndowns(researchGroupSelect.getValue());
  }

  if (isDrpm) {
    researchGroupSelect.unlock();
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
      loadResearchGroupDowndowns(researchGroupSelect.getValue());
      formValidate.clearErrors();
      researchGroup.focus();
    });
  });

  if (status !== DIF_STATES.UNSUBMITTED && !isDrpm) {
    const formFields = form.querySelectorAll('input, select, textarea, button');
    formFields.forEach((field) => {
      const formField = field;
      researchGroupSelect.disable();
      fundersSelect.disable();
      formField.disabled = true;
    });
  }

  geoViz.on('geojsonupdated', (e) => {
    const geometry = e.geojson ? e.geojson.geometry : '';

    if (!geometry) {
      document.getElementById('spatialExtentGeometry').value = '';
      return;
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
        document.getElementById('spatialExtentGeometry').value = gmlOutput;
      });
  });

  const dashboardUrl = Routing.generate('app_ui_dashboard');

  const fullScreenModal = new FullScreenModal({
    title: 'Welcome to the new Dataset Information Form (DIF)!',
    cookieName: 'new-dif-acknowledged-12MAY2026',
    content: `
    New Features Include:
      <ul style="list-style: disc; margin: 1rem 0; padding-left: 1.5rem;">
        <li>Cleaner look and feel</li>
        <li>Tabs on the side to navigate sections</li>
        <li>New spatial extent map</li>
      </ul>
      <p>
        Important Notes:
      </p>
      <p>
        A listing of datasets that you have created are now located on the
        <a href="${dashboardUrl}" class="pagelink" target="_blank">User Dashboard</a>.
        If you would like to work on a previously saved DIF, please use the
        <a href="${dashboardUrl}" class="pagelink" target="_blank">Dashboard</a> to load
        the DIF.
      </p>
    `,
  });

  window.addEventListener('load', () => {
    fullScreenModal.show();
  });
});
