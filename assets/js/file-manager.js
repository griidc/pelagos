import Vue from "vue";
import FileManager from "./vue/FileManager";
import axios from "axios";
import Dropzone from "dropzone";
import "../css/file-manager.css";

const fileManagerElement = document.getElementById("file-manager-app");

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
        url: Routing.generate('pelagos_api_post_chunks'),
        chunking: true,
        chunkSize: 1024*1024,
        forceChunking: true,
        parallelChunkUploads: true,
        retryChunks: true,
        retryChunksLimit: 3,
        maxFileSize: 1000000,
        autoQueue: false,
        chunksUploaded: function(file, done) {
            // All chunks have been uploaded. Perform any other actions
            let currentFile = file;
            const axiosInstance = axios.create({});

            axiosInstance.get(Routing.generate('pelagos_api_combine_chunks')
                + "/"
                + datasetSubmissionId
                + "?dzuuid=" + currentFile.upload.uuid
                + "&dztotalchunkcount=" + currentFile.upload.totalChunkCount
                + "&fileName=" + currentFile.name
                + "&dztotalfilesize=" + currentFile.upload.total)
                .then(response => {
                    axiosInstance
                        .post(
                            Routing.generate('pelagos_api_add_file_dataset_submission')
                            + "/"
                            + datasetSubmissionId,
                            response.data
                        )
                        .then(response => {
                            done();
                        }).catch(error => {
                            currentFile.accepted = false;
                            myDropzone._errorProcessing([currentFile], error.message);
                    });
                })
        },
    });

    myDropzone.on("addedfile", function(file) {
        const axiosInstance = axios.create({});

        axiosInstance.get(
            Routing.generate('pelagos_api_check_file_exists_dataset_submission')
            + "/"
            + datasetSubmissionId,
            {
                params: {
                    name: file.name
                }
            }).then(response => {
                if (response.data === false) {
                    myDropzone.enqueueFile(file);
                } else {
                    alert('File already exists with same name');
                }
            }).catch( error => {
                alert(error);
            });
    });
}
