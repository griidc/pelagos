import '../../scss/dif.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';

import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

//When ready
document.addEventListener('DOMContentLoaded', function () {
    new TomSelect('#research-group',{
		valueField: 'id',
		labelField: 'name',
		searchField: ['name'],
        preload: true,
        maxOptions: null,
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
});