<template>
    <div ref="dev">
        <DxFileManager
                :file-system-provider="customFileProvider"
                :on-error-occurred="onErrorOccurred"
        >
            <DxPermissions :delete="true" :upload="true"/>
        </DxFileManager>
    </div>
</template>

<script>
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import { DxFileManager, DxPermissions } from "devextreme-vue/file-manager";
import CustomFileSystemProvider from 'devextreme/file_management/custom_provider';
import Dropzone from "dropzone";

const axiosInstance = axios.create({});
let datasetSubmissionId = null;

export default {
    name: "FileManager",
    components: {
        DxFileManager,
        DxPermissions,
        CustomFileSystemProvider
    },

    data() {
        return {
            customFileProvider: new CustomFileSystemProvider({
                getItems,
                deleteItem,
                uploadFileChunk
            })
        };
    },

    props: {
        datasetSubId: {},
    },

    created() {
        // Assigning it to the global variable of this class so that functions outside the export can use it
        datasetSubmissionId = this.datasetSubId;
    },

    methods: {
        onErrorOccurred: function(e) {
            e.errorText = 'Cannot delete folders';
            return e;
        }
    }
};

const getItems = (pathInfo) => {
    return new Promise((resolve, reject) => {
        axiosInstance
            .get(`${Routing.generate('pelagos_api_get_files_dataset_submission')}/${datasetSubmissionId}?path=${pathInfo.path}`)
            .then(response => {
                resolve(response.data);
            }).catch(error => {
                reject(error);
        })
    })
}

const deleteItem = (item) => {
    return new Promise((resolve, reject) => {
        if (item.isDirectory === false) {
            axiosInstance
                .get(`${Routing.generate('pelagos_api_get_file_dataset_submission')}/${datasetSubmissionId}?path=${item.path}`)
                .then(response => {
                    axiosInstance
                        .delete(`${Routing.generate('pelagos_api_datasets_delete')}/${response.data.id}`)
                        .then(() => {
                            resolve();
                        })
                }).catch(error => {
                    reject(error)})
        } else {
            reject(new Error('Cannot delete folders'));
        }
    })
}

const uploadFileChunk = (fileData, uploadInfo, destinationDirectory) => {
    return new Promise((resolve, reject) => {
        let myDropzone = new Dropzone("div#dropzone-uploader", {
            url: Routing.generate('pelagos_api_post_chunks'),
            chunking: true,
            chunkSize: 1024 * 1024,
            forceChunking: true,
            parallelChunkUploads: true,
            retryChunks: true,
            retryChunksLimit: 3,
            maxFileSize: 1000000,
            autoQueue: false,
            chunksUploaded: function (file, done) {
                // All chunks have been uploaded. Perform any other actions
                let currentFile = file;
                const axiosInstance = axios.create({});
                const fileName = () => {
                    let fileName = '';
                    if (destinationDirectory.path) {
                        fileName = `${destinationDirectory.path}/`;
                    }
                    fileName += currentFile.fullPath ?? currentFile.name;
                    return fileName;
                };
                axiosInstance.get(`${Routing.generate('pelagos_api_combine_chunks')}/${datasetSubmissionId}` +
                    `?dzuuid=${currentFile.upload.uuid}` +
                    `&dztotalchunkcount=${currentFile.upload.totalChunkCount}` +
                    `&fileName=${fileName()}` +
                    `&dztotalfilesize=${currentFile.upload.total}`)
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
                                resolve();
                            }).catch(error => {
                            currentFile.accepted = false;
                            myDropzone._errorProcessing([currentFile], error.message);
                        });
                    })
            },
        });
        myDropzone.on("addedfile", function (file) {
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
            }).catch(error => {
                alert(error);
            });
        });
    });
}

</script>

<style>

</style>
