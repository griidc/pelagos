import '../../scss/dif.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';

import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';

document.addEventListener('DOMContentLoaded', () => {
  const fundersSelect = new TomSelect('#funders', {
    valueField: 'id',
    labelField: 'name',
    searchField: ['name'],
    preload: true,
    maxOptions: null,
    plugins: {
      clear_button: {
        title: 'Remove all selected options',
      },
    },
    // fetch remote data
    load(query, callback) {
      const url = Routing.generate('pelagos_dif_get_funders');
      fetch(url)
        .then((response) => response.json())
        .then((json) => {
          callback(json.Funders);
        }).catch(() => {
          callback();
        });
    },
  });

  const researchGroupSelect = new TomSelect('#researchGroup', {
    valueField: 'id',
    labelField: 'name',
    searchField: ['name'],
    preload: true,
    maxOptions: null,
    plugins: [
      'clear_button',
      'dropdown_input',
    ],
    // fetch remote data
    load(query, callback) {
      const url = Routing.generate('pelagos_dif_get_research_groups');
      fetch(url)
        .then((response) => response.json())
        .then((json) => {
          callback(json.ResearchGroups);
        }).catch(() => {
          callback();
        });
    },
  });

  function populateResearchGroupContacts(contacts) {
    const pointOfContactDropdowns = document.querySelectorAll('.point-of-contact');
    pointOfContactDropdowns.forEach((element) => {
      const dropdown = element;
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
        dropdown.appendChild(option);
      });
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

  // on form reset event
  const difForm = document.getElementById('difForm');
  difForm.addEventListener('reset', () => {
    // reset tomselects
    setTimeout(() => {
      researchGroupSelect.clear();
      fundersSelect.clear();
      populateResearchGroupContacts([]);
    });
  });
});
