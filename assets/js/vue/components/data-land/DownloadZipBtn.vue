<template>
    <div>
        <button @click="randomBtn">CLick me</button>
        <DxPopup
            title="Duplicate Filenames"
            :visible.sync="showPopup"
            :close-on-outside-click="true"
            :show-title="true"
            :width="500"
            :height="200">
            <template>
                <p>
                    Hello Wrld!
                </p>
            </template>
        </DxPopup>
    </div>
</template>

<script>
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import {DxPopup} from 'devextreme-vue/popup';

export default {
    name: "DownloadZipBtn",
    components: {
        DxPopup
    },
    data() {
        return {
            showPopup: false
        }
    },
    methods: {
        randomBtn: function () {
            this.showPopup = true;
        }
    }
}
</script>

<style scoped>

</style>
