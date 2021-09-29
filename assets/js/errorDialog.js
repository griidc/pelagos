import '@fortawesome/fontawesome-free/css/all.min.css';
import template from '../html/errorDialog.html';

require('devextreme/dist/css/dx.light.css');

const Popup = require('devextreme/ui/popup');
// const Button = require('devextreme/ui/button');

const errorDialogDiv = document.createElement('div');
document.body.appendChild(errorDialogDiv);

const errorPopup = new Popup(errorDialogDiv, {
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

const showError = (message) => {
  errorPopup.show();
  document.getElementById('errorDialogMessage').innerHTML = message;
};

export default showError;
