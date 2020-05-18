<template>
    <b-card-text class="vh-100">
        <ResultRow :resultRowData="dataset" v-for="dataset in datasets" v-bind:key="dataset.udi"/>
    </b-card-text>
</template>

<script>
    const axios = require('axios');
    import ResultRow from "./../search/ResultRow";
    export default {
        name: "DatasetsTab",
        components: { ResultRow },
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
