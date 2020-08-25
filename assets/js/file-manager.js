import Vue from "vue";
import FileManager from "./vue/FileManager";
const fileManagerElement = document.getElementById("file-manager-app");
import axios from "axios";
import Dropzone from "dropzone";
import "../css/file-manager.css";


if (fileManagerElement.dataset.id) {
    const datasetSubmissionId = Number(fileManagerElement.dataset.id);

        new Vue({
        el: '#file-manager-app',
        data() {
            return {
                fileItems: this.getFileItems(),
                showFileManager: false,
                datasetSubmissionId: datasetSubmissionId
            }
        },
        components: { FileManager },
        template: `<FileManager v-if="showFileManager" :files="fileItems"/>`,
        methods: {
            getFileItems: function () {
                const axiosInstance = axios.create({});
                axiosInstance
                    .get(Routing.generate('pelagos_api_get_files_dataset_submission') + "/" + datasetSubmissionId)
                    .then(response => {
                        // this.fileItems = Object.assign({}, response.data)
                        this.fileItems = response.data;
                        this.showFileManager = true;
                    }).catch(error => {
                        console.log(error);
                });
            }
        },
    });

    let myDropzone = new Dropzone("div#dropzone-uploader", {
        url: Routing.generate('pelagos_api_post_files_dataset_submission') + "/" + datasetSubmissionId,
        chunking: true,
        chunkSize: 1024*1024,
        forceChunking: false,
        parallelChunkUploads: true,
        retryChunks: true,
        retryChunksLimit: 3
    });
}
