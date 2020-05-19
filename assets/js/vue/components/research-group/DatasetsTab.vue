<template>
    <b-card-text class="vh-100">
        <DatasetRow :datasetRowData="dataset" v-for="dataset in datasets" v-bind:key="dataset.udi"/>
    </b-card-text>
</template>

<script>
    const axios = require('axios');
    import DatasetRow from "./../search/DatasetRow";
    export default {
        name: "DatasetsTab",
        components: { DatasetRow },
        props: {
            datasets: {}
        },
        data() {
            return {
                datasetsRetrievedInfo: []
            }
        },
        methods: {
            getDatalandRoute: function (udi) {
                return Routing.generate("pelagos_app_ui_dataland_default", {'udi' : udi });
            }
        }
    }
</script>

<style scoped>

</style>
