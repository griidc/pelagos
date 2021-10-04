import 'devextreme/dist/css/dx.light.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import Popup from 'devextreme/ui/popup';

const errorTemplate = `
<div>
    <p>
        <i class="fas fa-exclamation-triangle fa-2x" style="color:#d9534f"></i>&nbsp;
    </p>
    <span id="errorDialogMessage">
        Sorry the server is broken<br>
        come back later!<br>
        Or e-mail fake@mail.com
    </span>
</div>
`;

const errorDialogDiv = document.createElement('div');
document.body.appendChild(errorDialogDiv);

const errorPopup = new Popup(errorDialogDiv, {
  contentTemplate: errorTemplate,
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

const showError = (message = null, showLogoutButton = false) => {
  if (showLogoutButton) {
    const toolbarItems = errorPopup.option('toolbarItems');
    toolbarItems[0].visible = showLogoutButton;
    errorPopup.option('closeOnOutsideClick', !showLogoutButton);
    errorPopup.option('showCloseButton', !showLogoutButton);
    errorPopup.option('title', 'Session Expired!');
    errorPopup.option('toolbarItems', toolbarItems);
  }
  errorPopup.show();
  if (message) {
    document.getElementById('errorDialogMessage').innerHTML = message;
  }
};

export default showError;
