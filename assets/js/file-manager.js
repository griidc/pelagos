import Vue from "vue";
import FileManager from "./vue/FileManager";
import "../css/file-manager.css";
import '@fortawesome/fontawesome-free/css/all.min.css';

const fileManagerElement = document.getElementById("file-manager-app");

if (fileManagerElement) {
    if (fileManagerElement.dataset) {
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
}
