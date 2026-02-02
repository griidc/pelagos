import '../../scss/dif.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';

import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

//When ready
document.addEventListener('DOMContentLoaded', function () {

    const fundersSelect = new TomSelect('#funders',{
		valueField: 'id',
		labelField: 'name',
		searchField: ['name'],
        preload: true,
        maxOptions: null,
        plugins: {
            'clear_button':{
                'title':'Remove all selected options',
            },
        },
		// fetch remote data
		load: function(query, callback) {
            var url = Routing.generate('pelagos_dif_get_funders');
			fetch(url)
				.then(response => response.json())
				.then(json => {
					callback(json.Funders);
				}).catch(()=>{
					callback();
				});
		},
	});

    const researchGroupSelect = new TomSelect('#researchGroup',{
		valueField: 'id',
		labelField: 'name',
		searchField: ['name'],
        preload: true,
        maxOptions: null,
        plugins: [
            'clear_button',
            'dropdown_input'
        ],
		// fetch remote data
		load: function(query, callback) {
            var url = Routing.generate('pelagos_dif_get_research_groups');
			fetch(url)
				.then(response => response.json())
				.then(json => {
					callback(json.ResearchGroups);
				}).catch(()=>{
					callback();
				});
		},
	});

    researchGroupSelect.on('change', function(value) {
        loadResearchGroupDowndowns(value);
    });

    function loadResearchGroupDowndowns(researchGroupId) {
        const url = Routing.generate('pelagos_dif_get_research_group_contacts', { id: researchGroupId });
        let contacts = [];
        fetch(url)
            .then(response => response.json())
            .then(json => {
                contacts = json.Contacts;
                // populateResearchGroupContacts(json.Contacts);
            })
            .catch(error => console.error('Error loading data:', error))
            .finally(() => {
                populateResearchGroupContacts(contacts);
            });
    }

    function populateResearchGroupContacts(contacts) {
        const pointOfContactDropdowns = document.querySelectorAll('.point-of-contact');
            pointOfContactDropdowns.forEach(dropdown => {
                dropdown.innerHTML = '';
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                if (contacts.length === 0) {
                    defaultOption.textContent = '[PLEASE SELECT A PROJECT FIRST]';
                    defaultOption.disabled = true;
                    dropdown.disabled = true;
                } else  {
                    defaultOption.textContent = '[PLEASE SELECT A CONTACT]';
                    dropdown.disabled = false;
                }
                defaultOption.selected = true;
                dropdown.appendChild(defaultOption);
                contacts.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name + ' (' + item.email + ')';
                    dropdown.appendChild(option);
                });
            });
    }

});