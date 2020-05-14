<template>
    <b-card class="card-product" @click="openUrl(url)">
        <div>
            <span class="badge badge-secondary"
                  v-if="resultRowData.availabilityStatus === 0"
                  v-tooltip="{
                      content: 'This dataset has not been submitted and is not available for download',
                      placement:'top'
                  }">
                Identified
            </span>
            <span class="badge badge-primary"
                  v-else-if="resultRowData.availabilityStatus === 2 || resultRowData.availabilityStatus === 4"
                  v-tooltip="{
                      content: 'This dataset has been submitted and is not available for download',
                      placement:'top'
                  }">
                Submitted
            </span>
            <span class="badge badge-danger"
                  v-else-if="resultRowData.availabilityStatus === 5 || resultRowData.availabilityStatus === 8"
                  v-tooltip="{
                      content: 'This dataset is restricted for download',
                      placement:'top'
                  }">
                Restricted
            </span>
            <span class="badge badge-success"
                  v-else-if="resultRowData.availabilityStatus === 7 || resultRowData.availabilityStatus === 10"
                  v-tooltip="{
                      content: 'This dataset is available for download',
                      placement:'top'
                  }">
                Available
            </span>
        </div>
        <b-card-title>{{ resultRowData.title }}</b-card-title>
        <b-card-text class="d-flex justify-content-between" >
            <div v-if="Object.keys(resultRowData.datasetSubmission).length > 0">
                <div v-if="resultRowData.datasetSubmission.authors">
                    Authors: {{ resultRowData.datasetSubmission.authors }}
                </div>
                <div v-if="resultRowData.acceptedDate">
                    Published on {{ resultRowData.acceptedDate }}
                </div>
                <div v-if="resultRowData.fileFormat">
                    File Format: {{ resultRowData.fileFormat }}
                </div>
            </div>
            <div>
                <div v-if="resultRowData.doi.doi">
                    DOI: {{ resultRowData.doi.doi }}
                </div>
                <div>
                    UDI: {{ resultRowData.udi }}
                </div>
                <div v-if="resultRowData.fileSize">
                    File Size: {{ resultRowData.fileSize }}
                </div>
            </div>
        </b-card-text>
    </b-card>
</template>

<script>
    export default {
        name: "ResultRow",
        props: {
            resultRowData: {
                type: Object
            }
        },
        data: function () {
            return {
                url: Routing.generate("pelagos_app_ui_dataland_default", {'udi' : this.resultRowData.udi } )
            }
        },
        methods: {
            openUrl: function(url) {
                if ("" === window.getSelection().toString()) {
                    window.open(url, '_BLANK');
                }
            }
        }
    }
</script>

<style scoped>
    div.bootstrap p {
        margin-bottom: 0 !important;
    }

    div.bootstrap h4 {
        font-size: 1.3rem !important;
    }
</style>
