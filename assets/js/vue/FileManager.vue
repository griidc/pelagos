<template>
    <div>
        <DxFileManager :file-system-provider="customProvider">
            <DxUpload :chunk-size="500000"/>
            <DxPermissions
                :create="true"
                :copy="true"
                :move="true"
                :delete="true"
                :rename="true"
                :upload="true"
                :download="true"
            />
        </DxFileManager>
    </div>
</template>

<script>
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import {DxFileManager, DxPermissions, DxUpload} from "devextreme-vue/file-manager";
import ObjectFileSystemProvider from "devextreme/file_management/object_provider";
import CustomFileSystemProvider from "devextreme/file_management/custom_provider";
import axios from "axios";

export default {
    components: {
        DxFileManager,
        DxPermissions,
        DxUpload
    },

    data() {
        return {
            customProvider: {},
            fileManagerRefName: "fileManager",
        };
    },

    props: {
        files: {},
        datasetSubId: {
            type: Number
        }
    },

    created() {
        let objectProvider = new ObjectFileSystemProvider({
            data: this.files
        });

        this.customProvider = new CustomFileSystemProvider({
            getItems: parentDir => objectProvider.getItems(parentDir),
            createDirectory: (parentDir, dirName) =>
                objectProvider.createDirectory(parentDir, dirName),
            renameItem: (item, name) => objectProvider.renameItem(item, name),
            deleteItem: item => objectProvider.deleteItems([item]),
            copyItem: (item, destDir) => objectProvider.copyItems([item], destDir),
            moveItem: (item, destDir) => objectProvider.moveItems([item], destDir),
            uploadFileChunk: (file, uploadInfo, destDir) => {
                const axiosInstance = axios.create({});
                axiosInstance
                    .post(Routing.generate('pelagos_api_post_files_dataset_submission') + "/" + this.datasetSubId, {
                        file: this.makeFileObject(file, uploadInfo)
                    })
                    .then(response => {
                        console.log('success');
                    }).catch(error => {
                        console.log(error);
                });
                objectProvider.uploadFileChunk(file, uploadInfo, destDir)
            },
            abortFileUpload: (file, uploadInfo, destDir) =>
                objectProvider.abortFileUpload(file, uploadInfo, destDir),
            downloadItems: items => objectProvider.downloadItems(items)
        });
    },
    methods: {
        makeFileObject(file, chunkInfo) {
            return {
                name: file.name,
                size: file.size,
                lastModified: file.lastModified,
                lastModifiedDate: file.lastModifiedDate,
                chunkCount: chunkInfo.chunkCount,
                chunkIndex: chunkInfo.chunkIndex
            }
        }
    }
};
</script>
