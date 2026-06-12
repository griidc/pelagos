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

document.addEventListener('DOMContentLoaded', () => {
  const geoViz = new GeoViz(document.getElementById('leaflet-map'), {
    loadWizard: true,
  });

  const datasetContacts = document.getElementsByClassName('contactperson');

  Array.from(datasetContacts).forEach((contact) => {
    const contactSelect = new TomSelect(contact, {
      maxOptions: null,
    });
  });

  const funders = document.getElementById('funders');
  const fundersSelect = new TomSelect(funders, {
    maxOptions: null,
    plugins: {
      clear_button: {
        title: 'Remove all selected options',
      },
    },
  });

  const themeKeywords = document.getElementById('theme-keywords');
  const themeKeywordsSelect = new TomSelect(themeKeywords, {
    plugins: ['remove_button'],
    searchField: null,
    maxOptions: null,
    create: true,
    persist: false,
  });

  const placeKeywords = document.getElementById('place-keywords');
  const placeKeywordsSelect = new TomSelect(placeKeywords, {
    plugins: ['remove_button'],
    searchField: null,
    maxOptions: null,
    create: true,
    persist: false,
  });

  const topicKeywords = document.getElementById('topic-keyword-select');
  const topicKeywordsSelect = new TomSelect(topicKeywords, {
    plugins: ['remove_button'],
    maxOptions: null,
    create: false,
    persist: false,
  });
});
