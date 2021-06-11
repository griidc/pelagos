<template>
    <div>
        <b-button size="sm" class="mb-2" @click="randomBtn" variant="primary">
            <b-icon icon="arrow-up-right" aria-hidden="true" v-if="datasetInfo.remotelyHosted"></b-icon>
            <b-icon icon="download" aria-hidden="true" v-else></b-icon>
            {{ buttonTitle }}
        </b-button>
        <DxPopup
            :title="buttonTitle"
            :visible.sync="showPopup"
            :close-on-outside-click="true"
            :show-title="true"
            :width="500"
            :height="500">
            <template>
                <div v-if="datasetInfo.remotelyHosted">
                    <h3>The dataset you selected is hosted by an external repository.</h3>
                    <div>
                        <p>
                            All materials on this website are made available to GRIIDC and in turn to you "as-is."
                            By downloading files, you agree to the <a
                            href=https://data.gulfresearchinitiative.org/terms-and-conditions>GRIIDC Terms of
                            Service</a>.
                        </p>
                        <p>
                            This particular dataset is not hosted directly by GRIIDC, so additional terms and conditions
                            may be
                            imposed by the hosting entity.
                        </p>
                    </div>
                    <div>
                        <strong>UDI:</strong> {{ datasetInfo.dataset.udi }}<br>
                        <strong>Location:</strong>
                        <a :href=datasetInfo.fileUri target=_BLANK>
                            {{ datasetInfo.fileUri }}
                        </a><br>
                        <p>
                            {{ additionalInfo }}
                        </p>
                    </div>
                </div>
                <div v-else>
                    <div>
                        <p>
                            All materials on this website are made available to GRIIDC and in turn to you "as-is."
                            By downloading files, you agree to the
                            <a href=https://data.gulfresearchinitiative.org/terms-and-conditions>
                                GRIIDC Terms of Service
                            </a>
                        </p>
                    </div>
                    <hr>
                    <div>
                        <strong>UDI:</strong> {{ datasetInfo.dataset.udi }}<br>
                        <strong>File name:</strong> {{ datasetInfo.dataset.filename }}<br>
                        <strong>File size:</strong> {{ datasetInfo.dataset.fileSize }}<br>
                        <strong>SHA256 Checksum:</strong> {{ datasetInfo.dataset.checksum }}<br>
                        <strong>Estimated Download Time:</strong>{{ datasetInfo.dataset.fileSizeRaw }}<br>
                    </div>
                </div>
                <DxToolbarItem v-if="!datasetInfo.remotelyHosted"
                               widget="dxButton"
                               toolbar="bottom"
                               location="before"
                               :options="downloadButtonOptions"
                />
                <DxToolbarItem
                    widget="dxButton"
                    toolbar="bottom"
                    location="after"
                    :options="closeButtonOptions"
                />
            </template>
        </DxPopup>
    </div>
</template>

<script>
import "devextreme/dist/css/dx.common.css";
import "devextreme/dist/css/dx.light.css";
import {DxPopup, DxPosition, DxToolbarItem} from "devextreme-vue/popup";
import {getApi} from "@/vue/utils/axiosService";

export default {
    name: "DownloadZipBtn",
    components: {
        DxPopup,
        DxPosition,
        DxToolbarItem,
    },
    props: {
        id: {
            required: true
        }
    },
    data() {
        return {
            showPopup: false,
            datasetInfo: {
                remotelyHosted: false,
                dataset: {},
                fileUri: ""
            },
            buttonTitle: "",
            additionalInfo: "",
            downloadButtonOptions: {
                icon: 'download',
                text: 'Download',
                onClick: () => {
                    const url = `${Routing.generate('pelagos_api_file_zip_download_all')}/${this.datasetInfo.dataset.datasetSubmissionId}`;
                    const link = document.createElement('a');
                    link.href = url;
                    document.body.appendChild(link);
                    setTimeout(function() {
                        document.body.removeChild(link);
                        window.URL.revokeObjectURL(url);
                    }, 0);
                    link.click();
                    notify({
                        message: message,
                        position: {
                            my: 'center top',
                            at: 'center top'
                        }
                    }, 'success', 3000);
                }
            },
            closeButtonOptions: {
                text: 'Close',
                onClick: () => {
                    this.showPopup = false;
                }
            },
        }
    },
    created() {
        getApi(Routing.generate("pelagos_app_download_default", {"id": this.id})).then(response => {
            console.log(response.data);
            this.datasetInfo = response.data;
            if (this.datasetInfo.remotelyHosted) {
                this.buttonTitle = "External Dataset Link"
                if (this.datasetInfo.dataset.availability === 5) {
                    this.additionalInfo = `This dataset is restricted for download but is hosted by another
                    website so availability status is not guaranteed to be accurate. To obtain access to this dataset,
                    please click the location link above and follow any instructions provided.`;
                } else {
                    this.additionalInfo = `To download this dataset, please use the location link above.
                    Note, this dataset is not hosted at GRIIDC; the site is not under GRIIDC control and
                    GRIIDC is not responsible for the information or links you may find there.`;
                }
            } else {
                this.buttonTitle = "Download Zip"
            }
        });
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
