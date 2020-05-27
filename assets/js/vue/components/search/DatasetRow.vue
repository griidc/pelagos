<template>
    <b-card class="card-product" @click="openUrl(url)">
            <div>
                <span class="badge badge-secondary" v-if="datasetRowData.availabilityStatus === 0">Identified</span>
                <span class="badge badge-primary" v-else-if="datasetRowData.availabilityStatus === 2 || datasetRowData.availabilityStatus === 4">Submitted</span>
                <span class="badge badge-danger" v-else-if="datasetRowData.availabilityStatus === 5 || datasetRowData.availabilityStatus === 8">Restricted</span>
                <span class="badge badge-success" v-else-if="datasetRowData.availabilityStatus === 7 || datasetRowData.availabilityStatus === 10">Available</span>
            </div>
            <b-card-title style="font-size: 1.3rem !important;">{{ datasetRowData.title }}</b-card-title>
            <b-card-text class="d-flex justify-content-between" >
                <div v-if="Object.keys(datasetRowData.datasetSubmission).length > 0">
                    <div v-if="datasetRowData.datasetSubmission.authors">
                        Authors: {{ datasetRowData.datasetSubmission.authors }}
                    </div>
                    <div v-if="datasetRowData.acceptedDate">
                        Published on {{ datasetRowData.acceptedDate }}
                    </div>
                    <div v-if="datasetRowData.fileFormat">
                        File Format: {{ datasetRowData.fileFormat }}
                    </div>
                </div>
                <div>
                    <div v-if="datasetRowData.availabilityStatus !== 0 && datasetRowData.doi.doi">
                        DOI: {{ datasetRowData.doi.doi }}
                    </div>
                    <div>
                        UDI: {{ datasetRowData.udi }}
                    </div>
                    <div v-if="datasetRowData.fileSize">
                        File Size: {{ datasetRowData.fileSize }}
                    </div>
                </div>
            </b-card-text>
    </b-card>
</template>

<script>
    export default {
        name: "DatasetRow",
        props: {
            datasetRowData: {
                type: Object
            }
        },
        data: function () {
            return {
                url: Routing.generate("pelagos_app_ui_dataland_default", {'udi' : this.datasetRowData.udi } )
            }
        },
        methods: {
            openUrl: function(url) {
                if ("" === window.getSelection().toString()) {
                    window.open(url, '_blank');
                }
            }
        }
    }
</script>

<style scoped>
    .card-product {
        margin-bottom: 1rem;
    }

    .card-product:hover .btn-overlay {
        opacity: 1;
    }

    .card-product:hover {
        box-shadow: 0 4px 15px rgba(153, 153, 153, 0.3);
        transition: .5s;
        cursor: pointer;
    }

    a.search-result {
        font-size: 1.05em;
        text-decoration: none !important;
        color: black !important;
    }

    .search-result a:hover {
        text-decoration: none !important;
    }
</style>
