import Vue from "vue";
import FileManager from "./vue/FileManager";

new Vue({
    el: '#file-manager-app',
    components: { FileManager },
    template: `<FileManager/>`
});