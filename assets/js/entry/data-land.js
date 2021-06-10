import Vue from "vue";
import FileManager from "@/vue/FileManager";
import '/assets/css/file-manager.css'
import '@fortawesome/fontawesome-free/css/all.min.css';
import 'bootstrap';

const fileManagerElement = document.getElementById("file-manager-app");

const datasetSubmissionId = Number(fileManagerElement.dataset.id);
new Vue({
    el: '#file-manager-app',
    data() {
        return {
            datasetSubmissionId: datasetSubmissionId,
        }
    },
    components: {FileManager},
    template: `<FileManager :datasetSubId="datasetSubmissionId" :writeMode="false"/>`
});
