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

const toggleAllExpanded = (expanded) => {
    const expandElements = document.querySelectorAll('.expandme:has(.fa-chevron-down)');
    const collapseElements = document.querySelectorAll('.expandme:has(.fa-chevron-up)');

    if (expanded) {
        expandElements.forEach((element) => {
            element.click();
        });
    } else {
        collapseElements.forEach((element) => {
            element.click();
        });
    }
};

const isAllExpanded = () => {
    const expandMe = document.querySelectorAll('.expandme');
    const expandElements = document.querySelectorAll('.expandme:has(.fa-chevron-down)');

    let isExpanded = expandElements.length === expandMe.length;

    return isExpanded;
}

document.querySelectorAll('.expandme').forEach((element) => {
    element.addEventListener('click', () => {
        console.log('Toggled all expanded to:', isAllExpanded());
    });
});

window.toggleAllExpanded = toggleAllExpanded;
