import '@fortawesome/fontawesome-free/css/all.min.css';
import template from '../html/errorDialog.html';

require('devextreme/dist/css/dx.light.css');

const Popup = require('devextreme/ui/popup');
// const Button = require('devextreme/ui/button');

const errorPopup = new Popup(document.createElement('div'), {
  contentTemplate: template,
  width: 400,
  height: 300,
  showTitle: true,
  title: 'Something went wrong!',
  visible: false,
  dragEnabled: true,
  closeOnOutsideClick: true,
  showCloseButton: true,
});

function showError(message) {
  errorPopup.show();
//   document.getElementById('errorDialogMessage').innerHTML = message;
}

function help() {
  // I do nothing!
}

export default { showError, help };
