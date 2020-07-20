import Vue from 'vue';
import Demo from './vue/demo.vue';

new Vue({
  el: '#filedemo',
  components: { Demo },
  template: '<Demo/>'
});

import Dropzone from "dropzone";

var myDropzone = new Dropzone("div#dropzonetest", {
    url: "/test",
    autoQueue: false,
    previewTemplate: document.querySelector('#preview-template').innerHTML,
});

myDropzone.on("addedfile", function(file) {
    console.log(this.files);
});

