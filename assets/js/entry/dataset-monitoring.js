import '../../scss/dataset-monitoring.scss';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';

window.Alpine = Alpine;
window.tippy = tippy;

Alpine.plugin(collapse);
Alpine.start();
