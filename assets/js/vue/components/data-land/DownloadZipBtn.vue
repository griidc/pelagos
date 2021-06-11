<template>
    <div>
        <b-button size="sm" class="mb-2" @click="randomBtn" variant="primary">
            <b-icon icon="arrow-down" aria-hidden="true"></b-icon> Download Zip
        </b-button>
        <DxPopup
            title="Download Zip"
            :visible.sync="showPopup"
            :close-on-outside-click="true"
            :show-title="true"
            :width="500"
            :height="200">
            <template>
                <div id="dataset_download_content">
                    <div>
                        <p>
                            All materials on this website are made available to GRIIDC and in turn to you "as-is."
                            By downloading files, you agree to the
                            <a href=https://data.gulfresearchinitiative.org/terms-and-conditions>
                                GRIIDC Terms of Service
                            </a>.
                        </p>
                    </div>

<!--                    <div>-->
<!--                        <strong>UDI:</strong> ${data.dataset.udi}<br>-->
<!--                        <strong>File name:</strong> ${data.dataset.filename}<br>-->
<!--                        <strong>File size:</strong> ${data.dataset.fileSize}<br>-->
<!--                        <strong>SHA256 Checksum:</strong> ${data.dataset.checksum}<br>-->
<!--                        <strong>Estimated Download Time:</strong> <span id="dl_time">${testDownload(data.dataset.fileSizeRaw)}</span><br>-->
<!--                    </div>-->
                </div>
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
    props: {
        udi: {
            required: true
        }
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
