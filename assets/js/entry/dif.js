import '../../scss/dif.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';

import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';

document.addEventListener('DOMContentLoaded', () => {
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
  const difForm = document.getElementById('difForm');
  difForm.addEventListener('reset', () => {
    // reset tomSelects
    setTimeout(() => {
      fundersSelect.clear();
      populateResearchGroupContacts([]);
    });
  });
});
