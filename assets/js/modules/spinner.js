import { Spinner } from 'spin.js';
import 'spin.js/spin.css';

const spinnerDiv = document.createElement('div');
spinnerDiv.style.display = 'none';
spinnerDiv.classList.add('spinnermodal');
document.body.appendChild(spinnerDiv);

new Spinner(
  {
    lines: 13, // The number of lines to draw
    length: 40, // The length of each line
    width: 15, // The line thickness
    radius: 50, // The radius of the inner circle
    corners: 1, // Corner roundness (0..1)
    rotate: 0, // The rotation offset
    direction: 1, // 1: clockwise, -1: counterclockwise
    color: 'black', // #rgb or #rrggbb or array of colors
    fadeColor: 'lightgray', // CSS color or array of colors
    speed: 1, // Rounds per second
    trail: 60, // Afterglow percentage
    shadow: true, // Whether to render a shadow
    hwaccel: true, // Whether to use hardware acceleration
    className: 'spinner', // The CSS class to assign to the spinner
    zIndex: 2000000000, // The z-index (defaults to 2000000000)
    top: '50%', // Top position relative to parent
    left: '50%', // Left position relative to parent

  },
).spin(spinnerDiv);

function hideSpinner() {
  spinnerDiv.style.display = 'none';
}

function showSpinner() {
  spinnerDiv.style.display = 'block';
}

export default { showSpinner, hideSpinner };
