<template>
    <div>
        <div id="upload-file-button"></div>
        <DxPopup
            :visible.sync="showHelpPopup"
            :drag-enabled="false"
            :close-on-outside-click="true"
            :show-title="true"
            :width="400"
            :height="350"
            title="New Uploader!">
            <template>
                <div id="textBlock">
                    <h3 style="margin-top: 0">
                        You can now upload multiple files/folders, add files to an existing dataset, or replace/delete
                        files already uploaded. New features include:
                    </h3>
                    <ul>
                        <li>
                            <strong>Upload</strong>: You can upload files/folders via drag and drop or the upload button
                            on the left side
                        </li>
                        <li>
                            <strong>Delete/Move/Rename</strong>: You can perform these actions via the toolbar button or
                            right click menu
                        </li>
                        <li>
                            <strong>Download Individual Files</strong>: Select a file and download via toolbar button or
                            right click menu
                        </li>
                    </ul>
                </div>
            </template>
        </DxPopup>
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
            title="Duplicate Filenames"
            :visible.sync="isRenamedPopupVisible"
            :close-on-outside-click="true"
            @hidden="onHideRename"
            :show-title="true"
            :width="500"
            :height="200">
            <template>
                <p>
                    <i class="fas fa-exclamation-triangle fa-2x" style="color:#d9534f"></i>&nbsp;
                    <i><b>{{ filesRenamed }}</b></i> duplicate filenames were detected in the uploaded files.<br>
                    The duplicate filenames have been appended with a (1,2,3...).<br>
                    Please check your files for duplicates!
                </p>
            </template>
        </DxPopup>
        <DxPopup
            :visible.sync="loadingVisible"
            :close-on-outside-click="false"
            :show-title="false"
            position="center"
            :showCloseButton="false"
            :width="350"
            height="auto"
            :drag-enabled="false"
            :shading="true"
            shading-color="rgba(0,0,0,0.4)"
        >
            <template>
                <div class="progress-dialog">
                    <p>
                        <b>Uploaded {{ humanSize(doneFileSize) }} of {{ humanSize(totalFileSize) }}</b>
                    </p>
                    <p>
                        <b>Uploaded file {{ doneFiles }} of {{ totalFiles }}</b>
                    </p>
                    <DxProgressBar
                        id="progress-bar-status"
                        :min="0"
                        :max="totalFileSize"
                        :value="doneFileSize"
                        width="100%"
                    />
                    <br>
                    <DxButton
                        text="Cancel Upload"
                        type="danger"
                        width="50%"
                        styling-mode="contained"
                        @click="stopProcess"
                    />
                </div>
            </template>
        </DxPopup>
        <DxPopup
            :visible.sync="downloadPopup"
            :close-on-outside-click="false"
            :show-title="false"
            position="center"
            :showCloseButton="false"
            :width="350"
            height="auto"
            :drag-enabled="false"
            :shading="true"
            shading-color="rgba(0,0,0,0.4)"
        >
            <template>
                <div class="progress-dialog">
                    <p>
                        <b>Downloaded {{ humanSize(downloadedSize) }} of {{ humanSize(totalDownloadSize) }}</b>
                    </p>
                    <DxProgressBar
                        :min="0"
                        :max="totalDownloadSize"
                        :value="downloadedSize"
                        width="100%"
                    />
                    <br>
                    <DxButton
                        text="Cancel Download"
                        type="danger"
                        width="50%"
                        styling-mode="contained"
                        @click="stopDownload"
                    />
                </div>
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
                <DxItem
                    widget="dxMenu"
                    :options="helpPopupButton"
                    location="after"
                />
            </DxToolbar>
        </DxFileManager>
    </div>
</template>

<script>
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import {
  DxFileManager, DxItem, DxPermissions, DxToolbar,
} from 'devextreme-vue/file-manager';
import { DxPopup } from 'devextreme-vue/popup';
import { DxButton } from 'devextreme-vue/button';
import { DxProgressBar } from 'devextreme-vue/progress-bar';
import CustomFileSystemProvider from 'devextreme/file_management/custom_provider';
import Dropzone from 'dropzone';
import xbytes from 'xbytes';
import {
  axiosService, deleteApi, downloadApi, getApi, postApi, putApi,
} from './utils/axiosService';

const { CancelToken } = axiosService;
let cancel;
let datasetSubmissionId = null;
let destinationDir = '';

const contextMenuItems = [
  'delete',
  'refresh',
  'move',
  'rename',
  'download',
];

let fileManagerResolve = [];

let myFileManager;
let myDropzone;

const getItems = (pathInfo) => new Promise((resolve, reject) => {
  getApi(
    // eslint-disable-next-line no-undef
    `${Routing.generate('pelagos_api_get_files_dataset_submission')}/${datasetSubmissionId}?path=${pathInfo.path}`,
  ).then((response) => {
    resolve(response.data);
    const filesTabValidator = document.getElementById('filesTabValidator');
    const datasetFileTransferType = document.getElementById('datasetFileTransferType');
    if (response.data.length > 0) {
      filesTabValidator.value = 'valid';
      datasetFileTransferType.value = 'upload';
      document.querySelector('label.error[for="filesTabValidator"]').remove();
    } else {
      filesTabValidator.value = '';
      filesTabValidator.classList.add('error');
      datasetFileTransferType.value = '';
    }
  }).catch((error) => {
    if (error.response) {
      myFileManager.$parent.showPopupError(error.response.data.message);
    }
    reject(error);
  });
});

const deleteItem = (item) => new Promise((resolve, reject) => {
  deleteApi(
    // eslint-disable-next-line no-undef
    `${Routing.generate('pelagos_api_file_delete')}/${datasetSubmissionId}?path=${item.path}&isDir=${item.isDirectory}`,
  ).then(() => {
    myFileManager.$parent.showDownloadZipBtn = false;
    resolve();
  }).catch((error) => {
    myFileManager.$parent.showPopupError(error.response.data.message);
    reject(error);
  });
});

const moveItem = (item, destinationDirectory) => new Promise((resolve, reject) => {
  const newFilePathName = (destinationDir.path) ? `${destinationDirectory.path}/${item.name}` : item.name;
  putApi(
    // eslint-disable-next-line no-undef
    `${Routing.generate('pelagos_api_file_update_filename')}/${datasetSubmissionId}`,
    { newFileFolderPathDir: newFilePathName, path: item.path, isDir: item.isDirectory },
  ).then(() => {
    myFileManager.$parent.showDownloadZipBtn = false;
    resolve();
  }).catch((error) => {
    myFileManager.$parent.showPopupError(error.response.data.message);
    reject(error);
  });
});

const renameItem = (item, name) => new Promise((resolve, reject) => {
  const newFilePathName = (item.parentPath) ? `${item.parentPath}/${name}` : name;
  putApi(
    // eslint-disable-next-line no-undef
    `${Routing.generate('pelagos_api_file_update_filename')}/${datasetSubmissionId}`,
    { newFileFolderPathDir: newFilePathName, path: item.path, isDir: item.isDirectory },
  ).then(() => {
    myFileManager.$parent.showDownloadZipBtn = false;
    resolve();
  }).catch((error) => {
    myFileManager.$parent.showPopupError(error.response.data.message);
    reject(error);
  });
});

const downloadItems = (items) => new Promise((resolve, reject) => {
  myFileManager.$parent.resetDownloadAttrs();
  let itemsProcessed = 0;
  myFileManager.$parent.totalDownloadSize = Object.values(items).reduce((t, { size }) => t + size, 0);
  myFileManager.$parent.totalDownloads = items.length;
  const progressItems = [];
  myFileManager.$parent.downloadPopup = true;

  items.forEach((item, key) => {
    getApi(
      // eslint-disable-next-line no-undef
      `${Routing.generate('pelagos_api_get_file_dataset_submission')}/${datasetSubmissionId}?path=${item.path}`,
    ).then((response) => {
      progressItems[key] = [];
      const config = {
        responseType: 'blob',
        onDownloadProgress(progressEvent) {
          progressItems[key].size = progressEvent.loaded;
          myFileManager.$parent.downloadedSize = Object.values(progressItems).reduce((t, { size }) => t + size, 0);
        },
        cancelToken: new CancelToken((c) => {
          // An executor function receives a cancel function as a parameter
          cancel = c;
        }),
      };
      downloadApi(
        // eslint-disable-next-line no-undef
        `${Routing.generate('pelagos_api_file_download')}/${response.data.id}`, config,
        // eslint-disable-next-line no-shadow
      ).then((response) => {
        if (myFileManager.$parent.downloadPopup) {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement('a');
          link.href = url;
          // eslint-disable-next-line no-use-before-define
          link.setAttribute('download', getFileNameFromHeader(response.headers));
          document.body.appendChild(link);
          link.click();
          itemsProcessed += 1;
          myFileManager.$parent.downloadedFiles += 1;
        }
      }).then(() => {
        if (items.length === itemsProcessed) {
          resolve();
          myFileManager.$parent.resetDownloadAttrs();
        }
        resolve();
      }).catch((error) => {
        myFileManager.$parent.showPopupError(error.response.data.message);
        reject(error);
      });
    }).catch((error) => {
      myFileManager.$parent.showPopupError(error.response.data.message);
      reject(error);
    });
  });
});

const uploadFileChunk = (fileData, uploadInfo, destinationDirectory) => {
  destinationDir = destinationDirectory.path;
  return new Promise((resolve) => {
    myFileManager.$parent.showDownloadZipBtn = false;
    fileManagerResolve.push(resolve);
  });
};

const initDropzone = () => {
  myDropzone = new Dropzone('div#dropzone-uploader', {
    // eslint-disable-next-line no-undef
    url: Routing.generate('pelagos_api_post_chunks'),
    chunking: true,
    chunkSize: 1024 * 1024 * 10,
    forceChunking: true,
    parallelChunkUploads: false,
    parallelUploads: 10,
    retryChunks: true,
    retryChunksLimit: 3,
    maxFilesize: null,
    clickable: '#upload-file-button',
    timeout: 0,
    error: function error(file, message, xhr) {
      if (xhr.status === 401) {
        myFileManager.$parent.showPopupError('Session expired! Please log in again!');
      }
    },
    uploadprogress(file) {
      if (file.xhr.status === 204) {
        if (myFileManager.$parent.totalFileSize
          > (myFileManager.$parent.doneFileSize + file.upload.chunks[0].bytesSent)) {
          myFileManager.$parent.doneFileSize += file.upload.chunks[0].bytesSent;
        } else {
          myFileManager.$parent.doneFileSize = myFileManager.$parent.totalFileSize;
        }
      }
    },
    chunksUploaded(file, done) {
      // All chunks have been uploaded. Perform any other actions
      const currentFile = file;
      let fileName = '';
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
      const chunkData = {};
      chunkData.dzuuid = currentFile.upload.uuid;
      chunkData.dztotalchunkcount = currentFile.upload.totalChunkCount;
      chunkData.fileName = fileName;
      chunkData.dztotalfilesize = currentFile.size;
      postApi(
        // eslint-disable-next-line no-undef
        `${Routing.generate('pelagos_api_add_file_dataset_submission')
        }/${
          datasetSubmissionId}`,
        chunkData,
      ).then((response) => {
        if (response.data.isRenamed === true) {
          myFileManager.$parent.filesRenamed += 1;
        }
        done();
      }).catch((error) => {
        currentFile.accepted = false;
        // eslint-disable-next-line no-underscore-dangle
        myDropzone._errorProcessing([currentFile], error.message);
      });
    },
  });

  myDropzone.on('addedfile', (file) => {
    myFileManager.$parent.queueFile(file.size);
  });

  myDropzone.on('processing', () => {
    myFileManager.$parent.loadingVisible = true;
  });

  myDropzone.on('success', () => {
    myFileManager.$parent.completeFile();
  });

  myDropzone.on('queuecomplete', function queueComplete() {
    myFileManager.instance.refresh();
    myFileManager.instance.repaint();
    this.removeAllFiles();
    myFileManager.$parent.isRenamedPopupVisible = myFileManager.$parent.filesRenamed > 0;
  });

  myDropzone.on('totaluploadprogress', (uploadProgress) => {
    if (uploadProgress === 100) {
      fileManagerResolve.forEach((fileResolve) => {
        // eslint-disable-next-line no-unused-expressions
        fileResolve.resolve;
      });
      fileManagerResolve = [];
    }
  });
};

// eslint-disable-next-line no-undef
$('#ds-submit').on('active', () => {
  myFileManager.instance.refresh();
  myFileManager.instance.repaint();
  if (localStorage.getItem('showHelpPopupFileManager') !== 'false') {
    myFileManager.$parent.showHelpPopup = true;
    localStorage.setItem('showHelpPopupFileManager', 'false');
  }
});

const getFileNameFromHeader = (headers) => {
  let filename = '';
  const disposition = headers['content-disposition'];
  if (disposition && disposition.indexOf('attachment') !== -1) {
    const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
    const matches = filenameRegex.exec(disposition);
    if (matches != null && matches[1]) {
      filename = matches[1].replace(/['"]/g, '');
    }
  }
  return filename;
};

export default {
  name: 'FileManager',
  components: {
    DxFileManager,
    DxPermissions,
    DxToolbar,
    DxItem,
    DxPopup,
    DxButton,
    DxProgressBar,
  },

  data() {
    return {
      customFileProvider: new CustomFileSystemProvider({
        getItems,
        deleteItem,
        uploadFileChunk,
        moveItem,
        renameItem,
        downloadItems,
      }),
      downloadZipOptions: this.getDownloadZipFiles(),
      showDownloadZipBtn: this.isDownloadZipVisible(),
      uploadSingleFileOptions: this.uploadSingleFile(),
      isPopupVisible: false,
      errorMessage: '',
      loadingVisible: false,
      uploadMessage: 'Uploading...',
      bytesMessage: '',
      doneFiles: 0,
      totalFiles: 0,
      totalFileSize: 0,
      doneFileSize: 0,
      filesRenamed: 0,
      helpPopupButton: this.getHelpPopupText(),
      showHelpPopup: false,
      isRenamedPopupVisible: false,
      cancelUploadBtn: {
        class: 'cancel-upload-btn',
      },
      downloadedSize: 0,
      totalDownloadSize: 0,
      downloadPopup: false,
    };
  },

  props: {
    datasetSubId: {},
    writeMode: {},
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
    onSelectionChanged(args) {
      const isDirectory = (fileItem) => fileItem.isDirectory;
      if (args.selectedItems.find(isDirectory)) {
        args.component.option('contextMenu.items', this.filterMenuItems());
      } else {
        args.component.option('contextMenu.items', contextMenuItems);
      }
    },

    humanSize(fileSize) {
      return xbytes(fileSize);
    },

    queueFile(fileSize) {
      this.totalFiles += 1;
      this.totalFileSize += fileSize;
    },

    completeFile() {
      this.doneFiles += 1;
    },

    managerReady() {
      this.loadingVisible = false;
      this.doneFiles = 0;
      this.totalFiles = 0;
      this.totalFileSize = 0;
      this.doneFileSize = 0;
    },

    stopProcess() {
      myDropzone.removeAllFiles(true);
      window.stop();
    },

    filterMenuItems() {
      return contextMenuItems.filter((item) => {
        if (item === 'delete' || item === 'refresh' || item === 'move' || item === 'rename') {
          return item;
        }
        return null;
      });
    },

    onDownloadZipBtnClick() {
      // eslint-disable-next-line no-undef
      const url = `${Routing.generate('pelagos_api_file_zip_download_all')}/${datasetSubmissionId}`;
      const link = document.createElement('a');
      link.href = url;
      document.body.appendChild(link);
      setTimeout(() => {
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
      }, 0);
      link.click();
    },

    getDownloadZipFiles() {
      return {
        items: [
          {
            text: 'Download All',
            icon: 'download',
          },
        ],
        onItemClick: this.onDownloadZipBtnClick,
      };
    },

    isDownloadZipVisible() {
      getApi(
        // eslint-disable-next-line no-undef
        `${Routing.generate('pelagos_api_check_zip_exists')}/${this.datasetSubId}`,
      ).then((response) => {
        this.showDownloadZipBtn = response.data;
      });
    },

    uploadSingleFile() {
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
                  type: 'file',
                },
              },
              {
                text: 'Upload Folder',
                icon: 'folder',
                options: {
                  type: 'folder',
                },
              },
            ],
          },
        ],
        onItemClick: this.onUploadBtnClick,
      };
    },

    onUploadBtnClick(toolBarItem) {
      const uploadType = toolBarItem.itemData.options ? toolBarItem.itemData.options.type : undefined;
      if (uploadType === 'file') {
        myDropzone.hiddenFileInput.removeAttribute('webkitdirectory');
        document.getElementById('upload-file-button').click();
      } else if (uploadType === 'folder') {
        myDropzone.hiddenFileInput.setAttribute('webkitdirectory', true);
        document.getElementById('upload-file-button').click();
      }
    },

    directoryChanged(args) {
      destinationDir = args.directory.path;
    },

    showPopupError(message) {
      this.errorMessage = message;
      this.isPopupVisible = true;
    },

    getHelpPopupText() {
      return {
        items: [
          {
            icon: 'help',
          },
        ],
        onItemClick: this.onHelpButtonClick,
      };
    },

    onHelpButtonClick() {
      this.showHelpPopup = true;
    },

    onHideRename() {
      this.filesRenamed = 0;
    },

    stopDownload() {
      cancel();
      this.resetDownloadAttrs();
    },

    resetDownloadAttrs() {
      this.downloadPopup = false;
      this.downloadedSize = 0;
      this.totalDownloadSize = 0;
    },
  },
};

</script>

<style>
.dx-datagrid.dx-gridbase-container {
    background-image: url("../../images/dropzone.png");
    background-position: center;
    background-repeat: no-repeat;
}

.progress-dialog {
    align-items: center;
    text-align: center;
}
</style>
