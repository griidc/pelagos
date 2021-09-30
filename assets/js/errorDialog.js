import '@fortawesome/fontawesome-free/css/all.min.css';
import 'devextreme/dist/css/dx.light.css';
import Popup from 'devextreme/ui/popup';
import template from '../html/errorDialog.html';

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
  toolbarItems: [{
    widget: 'dxButton',
    toolbar: 'bottom',
    visible: false,
    options: {
      type: 'danger',
      text: 'Continue to Login Form',
      onClick() {
        // eslint-disable-next-line no-undef
        window.location.href = Routing.generate('security_login', { destination: window.location.href });
      },
    },
  }],
});

const showError = (message, showLogoutButton = false) => {
  const toolbarItems = errorPopup.option('toolbarItems');
  errorPopup.option('closeOnOutsideClick', !showLogoutButton);
  errorPopup.option('showCloseButton', !showLogoutButton);
  toolbarItems[0].visible = showLogoutButton;
  errorPopup.option('toolbarItems', toolbarItems);
  errorPopup.show();
  document.getElementById('errorDialogMessage').innerHTML = message;
};

export default showError;
