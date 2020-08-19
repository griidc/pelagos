import Vue from "vue";
import FileManager from "./vue/FileManager";
const fileManagerElement = document.getElementById("file-manager-app");
import axios from "axios";

if (fileManagerElement) {
    new Vue({
        el: '#file-manager-app',
        data() {
            return {
                fileItems: this.getFileItems(),
                showFileManager: false
            }
        },
        components: { FileManager },
        template: `<FileManager v-if="showFileManager" :files="fileItems" />`,
        methods: {
            getFileItems: function () {
                const axiosInstance = axios.create({});
                axiosInstance
                    .get(Routing.generate('pelagos_api_get_files_dataset_submission') + "/" + Number(fileManagerElement.dataset.id))
                    .then(response => {
                        // this.fileItems = Object.assign({}, response.data)
                        this.fileItems = response.data;
                        this.showFileManager = true;
                    }).catch(error => {
                        console.log(error);
                });
            }
        },
    });
}
