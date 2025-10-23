import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '../modules/cardClick';
import '../../scss/landing-pages.scss';
import '../../css/activeInactive.css';
import '../../scss/dashboard.scss';

window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
     tippy('[data-tippy-content]', {
        //options
    });
});
