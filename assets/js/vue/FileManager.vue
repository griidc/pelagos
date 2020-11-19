<template>
    <div>
        <DxFileManager :file-system-provider="customFileProvider">
        </DxFileManager>
    </div>
</template>

<script>
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import { DxFileManager } from "devextreme-vue/file-manager";
import CustomFileSystemProvider from 'devextreme/file_management/custom_provider';

const axiosInstance = axios.create({});
let datasetSubId = null;

export default {
    name: "FileManager",
    components: {
        DxFileManager,
        CustomFileSystemProvider
    },

    data() {
        return {
            customFileProvider: new CustomFileSystemProvider({
                getItems
            })
        };
    },

    props: {
        datasetSubId: {},
    },

    created() {
        datasetSubId = this.datasetSubId;
    },
};

function getItems(pathInfo) {
    return new Promise((resolve, reject) => {
        axiosInstance
            .get(`${Routing.generate('pelagos_api_get_files_dataset_submission')}/${datasetSubId}`)
            .then(response => {
                resolve(response.data);
            }).catch(error => {
                reject(error);
        })
    })
}
</script>

<style>

</style>
