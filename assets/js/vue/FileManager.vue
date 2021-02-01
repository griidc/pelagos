<template>
    <div>
        <DxPopup
            title="Error"
            :visible.sync="isPopupVisible"
            :close-on-outside-click="true"
            :show-title="true"
            :width="300"
            :height="250">
            <template>
                <p>
                    <i class="fas fa-exclamation-triangle fa-2x" style="color:#d9534f"></i>&nbsp;
                    {{ errorMessage }}
                </p>
            </template>
        </DxPopup>
        <DxFileManager
                :file-system-provider="customFileProvider"
                :on-selection-changed="onSelectionChanged"
                ref="myFileManager"
        >
            <DxPermissions
                :delete="true"
                :upload="true"
                :move="true"
                :rename="true"
                :download="true"/>
            <DxToolbar>
                <DxItem name="upload" :visible="false"/>
                <DxItem
                    :visible="showDownloadZipBtn"
                    widget="dxMenu"
                    :options="downloadZipOptions"
                />
                <DxItem name="refresh"/>
                <DxItem name="separator" location="after"/>
                <DxItem name="switchView"/>
            </DxToolbar>
        </DxFileManager>
    </div>
</template>

<script>
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import { DxFileManager, DxPermissions, DxToolbar, DxItem, DxContextMenu } from "devextreme-vue/file-manager";
import { DxPopup } from 'devextreme-vue/popup';
import CustomFileSystemProvider from 'devextreme/file_management/custom_provider';
import Dropzone from "dropzone";

const axiosInstance = axios.create({});
let datasetSubmissionId = null;
let destinationDir = '';

let contextMenuItems = [
    "delete",
    "refresh",
    "move",
    "rename",
    "download"
];

let itemsChanged = false;

let myFileManager;

export default {
    name: "FileManager",
    components: {
        DxFileManager,
        DxPermissions,
        DxToolbar,
        DxItem,
        DxContextMenu,
        CustomFileSystemProvider,
        DxPopup
    },

    data() {
        return {
            customFileProvider: new CustomFileSystemProvider({
                getItems,
                deleteItem,
                uploadFileChunk,
                moveItem,
                renameItem,
                downloadItems
            }),
            downloadZipOptions: this.getDownloadZipFiles(),
            showDownloadZipBtn: this.isDownloadZipVisible(),
            isPopupVisible: false,
            errorMessage: ''
        };
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
        myFileManager = this.$refs.myFileManager;
    },

    methods: {
        onSelectionChanged: function (args) {
            const isDirectory = (fileItem) => {
                return fileItem.isDirectory;
            }
            if (args.selectedItems.find(isDirectory)) {
                args.component.option('contextMenu.items', this.filterMenuItems());
            } else {
                args.component.option('contextMenu.items', contextMenuItems);
            }
        },

        filterMenuItems: function () {
            return contextMenuItems.filter(item => {
                if (item === 'delete' || item === 'refresh' || item === 'move' || item === 'rename') {
                    return item;
                }
            })
        },

        onItemClick: function () {
            axiosInstance({
                url: `${Routing.generate('pelagos_api_file_zip_download_all')}/${datasetSubmissionId}`,
                method: 'GET',
                responseType: 'blob', // important
            }).then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', getFileNameFromHeader(response.headers));
                document.body.appendChild(link);
                link.click();
            });
        },

        getDownloadZipFiles: function () {
            return {
                items: [
                    {
                        text: 'Download All',
                        icon: 'download',
                    }
                ],
                onItemClick: this.onItemClick
            };
        },

        isDownloadZipVisible: function () {
            axiosInstance
                .get(`${Routing.generate('pelagos_api_check_zip_exists')}/${this.datasetSubId}`)
                .then(response => {
                    this.showDownloadZipBtn = response.data;
                });
        },

        showPopupError: function (message) {
            this.errorMessage = message;
            this.isPopupVisible = true;
        }
    },
};

const getItems = (pathInfo) => {
    return new Promise((resolve, reject) => {
        axiosInstance
            .get(`${Routing.generate('pelagos_api_get_files_dataset_submission')}/${datasetSubmissionId}?path=${pathInfo.path}`)
            .then(response => {
                resolve(response.data);
            }).catch(error => {
                myFileManager.$parent.showPopupError(error.response.data.message);
                reject(error);
            })
    })
}

const deleteItem = (item) => {
    return new Promise((resolve, reject) => {
        axiosInstance
            .delete(`${Routing.generate('pelagos_api_file_delete')}/${datasetSubmissionId}?path=${item.path}&isDir=${item.isDirectory}`)
            .then(() => {
                resolve();
            }).catch(error => {
                myFileManager.$parent.showPopupError(error.response.data.message);
                reject(error)
            })
    })
}

const moveItem = (item, destinationDir) => {
    return new Promise((resolve, reject) => {
        const newFilePathName = (destinationDir.path) ? `${destinationDir.path}/${item.name}` : item.name;
        axiosInstance
            .put(
                `${Routing.generate('pelagos_api_file_update_filename')}/${datasetSubmissionId}`,
                {'newFileFolderPathDir': newFilePathName, 'path': item.path, 'isDir': item.isDirectory }
            )
            .then(() => {
                resolve();
            }).catch(error => {
                myFileManager.$parent.showPopupError(error.response.data.message);
                reject(error)
            })
    })
}

const renameItem = (item, name) => {
    return new Promise((resolve, reject) => {
        const newFilePathName = (item.parentPath) ? `${item.parentPath}/${name}` : name;
        axiosInstance
            .put(
                `${Routing.generate('pelagos_api_file_update_filename')}/${datasetSubmissionId}`,
                {'newFileFolderPathDir': newFilePathName, 'path': item.path, 'isDir': item.isDirectory }
            )
            .then(() => {
                resolve();
            }).catch(error => {
                myFileManager.$parent.showPopupError(error.response.data.message);
                reject(error)
            })
    })
}

const downloadItems = (items) => {
    return new Promise((resolve, reject) => {
        items.forEach(item => {
            axiosInstance
                .get(`${Routing.generate('pelagos_api_get_file_dataset_submission')}/${datasetSubmissionId}?path=${item.path}`)
                .then(response => {
                    axiosInstance({
                        url: `${Routing.generate('pelagos_api_file_download')}/${response.data.id}`,
                        method: 'GET',
                        responseType: 'blob', // important
                    }).then((response) => {
                        const url = window.URL.createObjectURL(new Blob([response.data]));
                        const link = document.createElement('a');
                        link.href = url;
                        link.setAttribute('download', getFileNameFromHeader(response.headers));
                        document.body.appendChild(link);
                        link.click();
                    }).then(() => {
                        resolve();
                    });
                }).catch(error => {
                    myFileManager.$parent.showPopupError(error.response.data.message);
                    reject(error)
                })
        })
    })
}

const uploadFileChunk = (fileData, uploadInfo, destinationDirectory) => {
    destinationDir = destinationDirectory.path;
    return new Promise((resolve, reject) => {
        resolve();
    });
}

const initDropzone = () => {
    let myDropzone = new Dropzone("div#dropzone-uploader", {
        url: Routing.generate('pelagos_api_post_chunks'),
        chunking: true,
        chunkSize: 1024 * 1024,
        forceChunking: true,
        parallelChunkUploads: true,
        parallelUploads: 10,
        retryChunks: true,
        retryChunksLimit: 3,
        maxFilesize: null,
        clickable: false,
        timeout: 0,
        chunksUploaded: function (file, done) {
            // All chunks have been uploaded. Perform any other actions
            let currentFile = file;
            let fileName = '';
            const axiosInstance = axios.create({});
            if (destinationDir) {
                fileName = `${destinationDir}/`;
            }
            fileName += currentFile.fullPath ?? currentFile.name;
            let chunkData = {};
            chunkData['dzuuid'] = currentFile.upload.uuid;
            chunkData['dztotalchunkcount'] = currentFile.upload.totalChunkCount;
            chunkData['fileName'] = fileName;
            chunkData['dztotalfilesize'] = currentFile.upload.total;
            axiosInstance
                .post(
                    Routing.generate('pelagos_api_add_file_dataset_submission')
                    + "/"
                    + datasetSubmissionId,
                    chunkData
                )
                .then(response => {
                    done();
                }).catch(error => {
                    currentFile.accepted = false;
                    myDropzone._errorProcessing([currentFile], error.message);
            });
        },
    });

    myDropzone.on("queuecomplete", function () {
        myFileManager.instance.repaint();
    });
}

$("#ds-submit").on("active", function() {
    myFileManager.instance.repaint();
});

const getFileNameFromHeader = (headers) => {
    let filename = "";
    let disposition = headers['content-disposition'];
    if (disposition && disposition.indexOf('attachment') !== -1) {
        let filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
        let matches = filenameRegex.exec(disposition);
        if (matches != null && matches[1]) {
            filename = matches[1].replace(/['"]/g, '');
        }
    }
    return filename;
}

</script>

<style>

</style>
