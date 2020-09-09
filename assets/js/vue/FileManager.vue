<template>
    <div>
        <DxFileManager :file-system-provider="objectProvider">
        </DxFileManager>
    </div>
</template>

<script>
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import {DxFileManager, DxPermissions, DxUpload} from "devextreme-vue/file-manager";
import ObjectFileSystemProvider from "devextreme/file_management/object_provider";

export default {
    components: {
        DxFileManager,
        DxPermissions,
        DxUpload
    },

    data() {
        return {
            objectProvider: {},
            fileManagerRefName: "fileManager",
        };
    },

    props: {
        files: {},
    },

    created() {
        this.objectProvider = new ObjectFileSystemProvider({
            data: this.files
        });
    },
};
</script>

<style>

</style>
