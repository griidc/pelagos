<template>
    <div>
        <div id="upload-file-button"></div>
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
        <DxPopup
            title="Uploading Files"
            :visible.sync="loadingVisible"
            :close-on-outside-click="false"
            :show-title="false"
            position="center"
            :showCloseButton="false"
            :width="300"
            :height="250"
            :drag-enabled="false"
        >
            <template>
                <center>
                <p>
                    {{ uploadMessage }}
                </p>
                <p>
                    {{ bytesMessage }}
                </p>
                </center>
                <DxProgressBar
                  id="progress-bar-status"
                  :min="0"
                  :max="totalFileSize"
                  :value="doneFileSize"
                  width="90%"
                />
                <center>
                <DxButton
                    text="Cancel Upload!"
                    type="danger"
                    styling-mode="contained"
                    @click="stopProcess"
                 />
                 <p>
                    If the filesize is large? Click Cancel!
                 </p>
                 </center>
            </template>
        </DxPopup>
        <DxFileManager
                :file-system-provider="customFileProvider"
                :on-selection-changed="onSelectionChanged"
                :on-current-directory-changed="directoryChanged"
                :on-content-ready="managerReady"
                ref="myFileManager"
        >
            <DxPermissions
                :delete="writeMode"
                :upload="writeMode"
                :move="writeMode"
                :rename="writeMode"
                :download="true"/>
            <DxToolbar>
                <DxItem name="upload" :visible="false"/>
                <DxItem
                    :visible="showDownloadZipBtn"
                    widget="dxMenu"
                    :options="downloadZipOptions"
                />
                <DxItem
                    widget="dxMenu"
                    :options="uploadSingleFileOptions"
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
import { DxButton } from 'devextreme-vue/button';
import { DxProgressBar } from 'devextreme-vue/progress-bar';
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
let fileManagerResolve = [];
let totalFiles = 0;
let doneFiles = 0;

let totalFileSize = 0;
let doneFileSize = 0;

let myFileManager;
let myDropzone;

let mags = ' KMGTPEZY';

function humanSize(bytes, precision) {
  let magnitude = Math.min(Math.log(bytes) / Math.log(1024) | 0, mags.length - 1);
  let result = bytes / Math.pow(1024, magnitude);
  let suffix = mags[magnitude].trim() + 'B';
  return result.toFixed(precision) + suffix;
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
        DxPopup,
        DxButton,
        DxProgressBar
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
            uploadSingleFileOptions: this.uploadSingleFile(),
            isPopupVisible: false,
            errorMessage: '',
            loadingVisible: false,
            uploadMessage: "Uploading...",
            bytesMessage: "",
            totalFileSize: 0,
            doneFileSize: 0
        };
    },

    props: {
        datasetSubId: {},
        writeMode: {}
    },

    created() {
        // Assigning it to the global variable of this class so that functions outside the export can use it
        datasetSubmissionId = this.datasetSubId;
    },

    mounted() {
        if (this.writeMode) {
          initDropzone();
        }
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

        managerReady: function (args) {
            this.loadingVisible =  false;
        },

        stopProcess: function () {
            myDropzone.removeAllFiles(true);
        },

        filterMenuItems: function () {
            return contextMenuItems.filter(item => {
                if (item === 'delete' || item === 'refresh' || item === 'move' || item === 'rename') {
                    return item;
                }
            })
        },

        onDownloadZipBtnClick: function () {
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
                onItemClick: this.onDownloadZipBtnClick
            };
        },

        isDownloadZipVisible: function () {
            axiosInstance
                .get(`${Routing.generate('pelagos_api_check_zip_exists')}/${this.datasetSubId}`)
                .then(response => {
                    this.showDownloadZipBtn = response.data;
                });
        },

        uploadSingleFile: function () {
            return {
                items: [
                    {
                        visible: this.writeMode,
                        text: 'Upload',
                        icon: 'upload',
                        items: [
                            {
                                text: 'Upload File',
                                icon: 'doc',
                                options: {
                                    type: 'file'
                                }
                            },
                            {
                                text: 'Upload Folder',
                                icon: 'folder',
                                options: {
                                    type: 'folder'
                                }
                            }
                        ]
                    }
                ],
                onItemClick: this.onUploadBtnClick,
            };
        },

        onUploadBtnClick: function (toolBarItem) {
            const uploadType = toolBarItem.itemData.options ? toolBarItem.itemData.options.type : undefined;
            if (uploadType === 'file') {
                myDropzone.hiddenFileInput.removeAttribute("webkitdirectory");
                document.getElementById("upload-file-button").click();
            } else if (uploadType === 'folder') {
                myDropzone.hiddenFileInput.setAttribute("webkitdirectory", true);
                document.getElementById("upload-file-button").click();
            }
        },

        directoryChanged: function (args) {
            destinationDir = args.directory.path;
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
                myFileManager.$parent.showDownloadZipBtn = false;
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
                myFileManager.$parent.showDownloadZipBtn = false;
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
                myFileManager.$parent.showDownloadZipBtn = false;
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
        myFileManager.$parent.showDownloadZipBtn = false;
        fileManagerResolve.push(resolve);
    });
}

const initDropzone = () => {
    myDropzone = new Dropzone("div#dropzone-uploader", {
        url: Routing.generate('pelagos_api_post_chunks'),
        chunking: true,
        chunkSize: 1024 * 1024,
        forceChunking: true,
        parallelChunkUploads: true,
        parallelUploads: 10,
        retryChunks: true,
        retryChunksLimit: 3,
        maxFilesize: null,
        clickable: "#upload-file-button",
        timeout: 0,
        autoProcessQueue: true,
        chunksUploaded: function (file, done) {
            // All chunks have been uploaded. Perform any other actions
            let currentFile = file;
            let fileName = '';
            const axiosInstance = axios.create({});
            if (destinationDir) {
                fileName = `${destinationDir}/`;
            }
            if (currentFile.fullPath) {
                fileName += currentFile.fullPath ?? currentFile.name;
            } else if (currentFile.webkitRelativePath) {
                fileName += currentFile.webkitRelativePath;
            } else {
                fileName += currentFile.name;
            }
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

    myDropzone.on("addedfile", function (file) {
        totalFiles++;
        myFileManager.$parent.totalFileSize += file.size;
        myFileManager.$parent.uploadMessage = "Uploading " + doneFiles + " of " + totalFiles
    });

    myDropzone.on("processing", function (file) {
        myFileManager.$parent.loadingVisible =  true;
    });

    myDropzone.on("success", function (file) {
        doneFiles++;
        myFileManager.$parent.doneFileSize += file.size;
        myFileManager.$parent.uploadMessage = "Uploading " + doneFiles + " of " + totalFiles;
        myFileManager.$parent.bytesMessage = "Uploading " + humanSize(myFileManager.$parent.doneFileSize) + " of " + humanSize(myFileManager.$parent.totalFileSize);
    });

    myDropzone.on("queuecomplete", function () {
        myFileManager.instance.repaint();
        totalFiles = 0;
        doneFiles = 0;
        totalFileSize = 0;
        doneFileSize = 0;
    });

    myDropzone.on("totaluploadprogress", function (uploadProgress, totalBytes, totalBytesSent) {
        if (uploadProgress === 100) {
            fileManagerResolve.forEach(function(fileResolve) {
                fileResolve.resolve;
            });
            fileManagerResolve = [];
            this.removeAllFiles();
        }
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
