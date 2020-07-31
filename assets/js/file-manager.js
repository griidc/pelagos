import Vue from "vue";
import FileManager from "./vue/FileManager";
const fileManagerElement = document.getElementById("file-manager-app");

if (fileManagerElement) {
    new Vue({
        el: '#file-manager-app',
        data() {
            return {
                datasetSubId: 0
            }
        },
        created() {
            this.datasetSubId = Number(fileManagerElement.dataset.id);
        },
        components: { FileManager },
        template: `<FileManager :id="datasetSubId"/>`
    });
}
