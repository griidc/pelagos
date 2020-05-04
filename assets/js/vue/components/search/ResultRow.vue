<template>
    <b-card class="card-product">
        <b-link class="search-result"
                :href="url" target="_blank">
            <div>
                <span class="badge badge-secondary" v-if="resultRowData.availabilityStatus === 0">Identified</span>
                <span class="badge badge-primary" v-else-if="resultRowData.availabilityStatus === 2 || resultRowData.availabilityStatus === 4">Submitted</span>
                <span class="badge badge-danger" v-else-if="resultRowData.availabilityStatus === 5 || resultRowData.availabilityStatus === 8">Restricted</span>
                <span class="badge badge-success" v-else-if="resultRowData.availabilityStatus === 7 || resultRowData.availabilityStatus === 10">Available</span>
            </div>
            <b-card-title>{{ resultRowData.title }}</b-card-title>
            <b-card-text class="d-flex justify-content-between" >
                <div>
                    Authors: {{ resultRowData.datasetSubmission.authors }}
                </div>
                <div>
                    DOI: {{ resultRowData.doi.doi }}
                </div>
            </b-card-text>
            <b-card-text class="d-flex justify-content-between" >
                <div>
                    Published on {{ resultRowData.year }}
                </div>
                <div>
                    UDI: {{ resultRowData.udi }}
                </div>
            </b-card-text>
            <b-card-text class="d-flex justify-content-between" >
                <div>
                    File Format: {{ resultRowData.fileFormat }}
                </div>
                <div>
                    File Size: {{ resultRowData.fileSize }}
                </div>
            </b-card-text>
        </b-link>
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
