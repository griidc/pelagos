<template>
  <div>
    <DxFileManager :file-system-provider="customProvider" :ref="fileManagerRefName">
<!--      <DxPermissions-->
<!--          :create="true"-->
<!--          :copy="true"-->
<!--          :move="true"-->
<!--          :delete="true"-->
<!--          :rename="true"-->
<!--          :upload="true"-->
<!--          :download="true"-->
<!--      />-->
    </DxFileManager>
  </div>
</template>

<script>
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import { DxFileManager, DxPermissions } from "devextreme-vue/file-manager";
import ObjectFileSystemProvider from "devextreme/file_management/object_provider";
import CustomFileSystemProvider from "devextreme/file_management/custom_provider";
import { fileItems } from "../data.js";

let objectProvider = null;
createObjectProvider(fileItems);

const customProvider = new CustomFileSystemProvider({
  getItems: parentDir => objectProvider.getItems(parentDir),
  createDirectory: (parentDir, dirName) =>
      objectProvider.createDirectory(parentDir, dirName),
  renameItem: item => objectProvider.renameItem(item),
  deleteItem: item => objectProvider.deleteItems([item]),
  copyItem: (item, destDir) => objectProvider.copyItems([item], destDir),
  moveItem: (item, destDir) => objectProvider.moveItems([item], destDir),
  uploadFileChunk: (file, uploadInfo, destDir) =>
      objectProvider.uploadFileChunk(file, uploadInfo, destDir),
  abortFileUpload: (file, uploadInfo, destDir) =>
      objectProvider.abortFileUpload(file, uploadInfo, destDir),
  downloadItems: items => objectProvider.downloadItems(items)
});

function createObjectProvider(data) {
  objectProvider = new ObjectFileSystemProvider({
    data: data
  });
}

export default {
  components: {
    DxFileManager,
    DxPermissions,
  },

  data() {
    return {
      customProvider,
      fileManagerRefName: "fileManager"
    };
  },

  props: {
    id: {
      type: Number
    }
  },
};
</script>
