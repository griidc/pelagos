<template>
    <div ref="dev">
        <DxFileManager :file-system-provider="customFileProvider" :on-error-occurred="onErrorOccurred">
            <DxPermissions :delete="true"/>
        </DxFileManager>
    </div>
</template>

<script>
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import { DxFileManager, DxPermissions } from "devextreme-vue/file-manager";
import CustomFileSystemProvider from 'devextreme/file_management/custom_provider';

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
                deleteItem
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

</script>

<style>

</style>
