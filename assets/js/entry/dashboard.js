import '../../scss/landing-pages.scss';
import '../../css/activeInactive.css';
import '../../scss/dashboard.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import '../modules/cardClick';

window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();
