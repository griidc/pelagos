import Dropzone from "dropzone";

var myDropzone = new Dropzone("div#dropzonetest", {
    url: "/test",
    autoQueue: false,
    previewTemplate: document.querySelector('#preview-template').innerHTML,
});

myDropzone.on("addedfile", function(file) {
    console.log(this.files);
});