<template>
    <div class="container">
        <div class="row">
            <main class="col-lg-9 overflow-auto">
                <div class="pt-3">
                    <h2> {{ dataset.title }} </h2>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <div v-if="Object.keys(dataset.datasetSubmission).length > 0">
                        <div v-if="dataset.datasetSubmission.authors">
                            <strong>Authors:</strong> {{ dataset.datasetSubmission.authors }}
                        </div>
                        <div v-if="dataset.acceptedDate">
                            <strong>Published on </strong> {{ getFormattedDate(dataset.acceptedDate.date) }}
                        </div>
                        <div v-if="dataset.availabilityStatus !== 7 && dataset.datasetSubmission.distributionFormatName">
                            <strong>File Format:</strong> {{ dataset.datasetSubmission.distributionFormatName }}
                        </div>
                    </div>
                    <div>
                        <div v-if="dataset.availabilityStatus !== 0 && dataset.doi.doi">
                            <strong>DOI:</strong> {{ dataset.doi.doi }}
                        </div>
                        <div>
                            <strong>UDI:</strong> {{ dataset.udi }}
                        </div>
                        <div v-if="dataset.availabilityStatus !== 7 && dataset.datasetSubmission.fileset">
                            <strong>File Size:</strong> {{ getFileSize(dataset.datasetSubmission.fileset.zipFileSize) }}
                        </div>
                    </div>
                </div>
                <!-- SPATIAL EXTENT MAP -->
<!--                <div>-->
<!--                    -->
<!--                </div>-->
                <!--CITATION -->
                <!--                <div>-->
                <!--                    -->
                <!--                </div>-->
                <!--ABSTRACT -->
                <!--                <div>-->
                <!--                    -->
                <!--                </div>-->
                <!--PUBS -->
                <!--                <div>-->
                <!--                    -->
                <!--                </div>-->
                <div class="py-2" v-show="isFileMangerReady">
                    <h2>{{dataset.datasetSubmission.id }}</h2>
                    <FileManager :datasetSubId="16799" :writeMode="false"></FileManager>
                </div>
            </main>
            <aside class="col-lg-3">
                <b-card header="Downloads" header-bg-variant="primary" class="text-center text-light">
                    <b-card-text class="text-dark">
                        {{ downloadCount }}
                    </b-card-text>
                </b-card>
                <b-card header="Details" header-bg-variant="primary" class="text-center text-light">
                    <b-card-text class="text-dark">
                        <p>
                            Research Group:
                            <b-badge>{{ dataset.researchGroup.shortName }}</b-badge>
                        </p>
                        <p>
                            Funded By: <br>
                            <b-badge>{{ dataset.researchGroup.fundingCycle.fundingOrganization.shortName }}</b-badge>
                        </p>
                    </b-card-text>
                </b-card>
            </aside>
        </div>
    </div>
</template>

<script>
import {getApi} from "@/vue/utils/axiosService";
import moment from 'moment';
import xbytes from "xbytes";
import FileManager from "../FileManager";

export default {
    name: "Dataland",
    components: {FileManager},
    props: {
        udi: {
            type: String
        }
    },
    data() {
        return {
            dataset: {
                researchGroup: {
                    fundingCycle: {
                        fundingOrganization: {}
                    }
                },
                datasetSubmission: {
                    fileset: {}
                },
                doi: {}
            },
            downloadCount: 0,
            isFileMangerReady: false
        }
    },
    created() {
        getApi(
            `${Routing.generate('pelagos_api_datasets_get')}?udi=${this.udi}&_properties=datasetSubmission,
            doi,datasetSubmission.fileset, researchGroup, researchGroup.fundingCycle.fundingOrganization`,
            {thisComponent: this, addLoading: true}
        ).then(response => {
            console.log(response.data);
            this.dataset = response.data[0];
            this.isFileMangerReady = true;
            getApi(`${Routing.generate('pelagos_app_ui_dataland_download_count')}/${this.dataset.id}`, {
                thisComponent: this,
                addLoading: true
            })
                .then(response => {
                    this.downloadCount = response.data.downloadCount;
                })
        })
    },
    methods: {
        getFormattedDate: function (date) {
            return moment(date).format('MMMM Do YYYY');
        },
        getFileSize: function (fileSize) {
            return xbytes(fileSize);
        },
    }
}
</script>

<style scoped>
.card {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, .1), 0 1px 2px 0 rgba(0, 0, 0, .06);
    margin: 1rem;
}
</style>