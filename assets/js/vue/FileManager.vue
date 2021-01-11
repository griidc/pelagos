<template>
    <div>
        <div class="progress-bar" style="text-align: center;">
            <div class="progress-info">
                {{ progressStatus }}
            </div>
            <DxProgressBar
              id="progress-bar-status"
              :min="0"
              :max="100"
              :status-format="statusFormat"
              :value="progressValue"
              width="100%"
            />
        </div>
        <DxFileManager
                :file-system-provider="customFileProvider"
                :on-error-occurred="onErrorOccurred"
        >
            <DxPermissions :delete="true" :upload="true"/>
            <DxToolbar>
                <DxItem name="upload" :visible="false"/>
                <DxItem name="refresh"/>
                <DxItem name="separator" location="after"/>
                <DxItem name="switchView"/>
            </DxToolbar>
            <DxContextMenu>
                <DxItem name="upload" :visible="false"/>
                <DxItem name="delete" :visible="true"/>
                <DxItem name="refresh" :visible="true"/>
            </DxContextMenu>
        </DxFileManager>
    </div>
</template>

<script>
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import { DxFileManager, DxPermissions, DxToolbar, DxItem, DxContextMenu } from "devextreme-vue/file-manager";
import { DxProgressBar } from 'devextreme-vue/progress-bar';
import CustomFileSystemProvider from 'devextreme/file_management/custom_provider';
import Dropzone from "dropzone";

const axiosInstance = axios.create({});
let datasetSubmissionId = null;
let destinationDir = '';

function statusFormat(value) {
  return `Loading: ${ value * 100 }%`;
}

export default {
    name: "FileManager",
    components: {
        DxFileManager,
        DxPermissions,
        DxToolbar,
        DxItem,
        DxContextMenu,
        CustomFileSystemProvider,
        DxProgressBar
    },

    data() {
        return {
            customFileProvider: new CustomFileSystemProvider({
                getItems,
                deleteItem,
                uploadFileChunk
            }),
            statusFormat
        };
    },
    
    computed: {
        progressValue() {
            return 50;
        },
        progressStatus() {
            return "Uploading"
        }
    },

    props: {
        datasetSubId: {},
    },

    created() {
        // Assigning it to the global variable of this class so that functions outside the export can use it
        datasetSubmissionId = this.datasetSubId;
    },

    mounted() {
        initDropzone();
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
                    reject(error)
                })
        } else {
            reject(new Error('Cannot delete folders'));
        }
    })
}

let fileManagerResolve;

const uploadFileChunk = (fileData, uploadInfo, destinationDirectory) => {
    destinationDir = destinationDirectory.path;
    return new Promise((resolve, reject) => {
        fileManagerResolve = resolve;
    });
}

const initDropzone = () => {
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
        clickable: false,
        chunksUploaded: function (file, done) {
            // All chunks have been uploaded. Perform any other actions
            let currentFile = file;
            let fileName = '';
            const axiosInstance = axios.create({});
            if (destinationDir) {
                fileName = `${destinationDir}/`;
            }
            fileName += currentFile.fullPath ?? currentFile.name;
            axiosInstance.get(`${Routing.generate('pelagos_api_combine_chunks')}/${datasetSubmissionId}` +
                `?dzuuid=${currentFile.upload.uuid}` +
                `&dztotalchunkcount=${currentFile.upload.totalChunkCount}` +
                `&fileName=${fileName}` +
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
                        }).catch(error => {
                        currentFile.accepted = false;
                        myDropzone._errorProcessing([currentFile], error.message);
                    });
                })
        }
    });
    myDropzone.on("addedfile", function (file) {
        const axiosInstance = axios.create({});
        axiosInstance.get(
            Routing.generate('pelagos_api_check_file_exists_dataset_submission')
            + "/"
            + datasetSubmissionId,
            {
                params: {
                    name: file.fullPath ?? file.name
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
    
    myDropzone.on("totaluploadprogress", function (uploadProgress, totalBytes, totalBytesSent) {
        console.log(uploadProgress);
        console.log(totalBytes);
        console.log(totalBytesSent);
        
        if (uploadProgress == 100) {
            fileManagerResolve.resolve();
            this.removeAllFiles();
        }
    });

}

</script>

<style>

</style>
