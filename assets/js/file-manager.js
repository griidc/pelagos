import Vue from "vue";
import FileManager from "./vue/FileManager";
import "../css/file-manager.css";

const fileManagerElement = document.getElementById("file-manager-app");

if (fileManagerElement.dataset.id) {
    const datasetSubmissionId = Number(fileManagerElement.dataset.id);
    new Vue({
        el: '#file-manager-app',
        data() {
            return {
                datasetSubmissionId: datasetSubmissionId
            }
        },
        components: {FileManager},
        template: `
          <FileManager :datasetSubId="datasetSubmissionId"/>`
    });
}
