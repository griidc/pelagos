<template>
    <div class="row">
        <div class="col-xl-12 border border-dark">
            <dataset-over-time />
        </div>
        <div class="col-xl-12 border border-dark">
            <dataset-size-ranges />
        </div>
        <div class="col-xl-12 border border-dark">
            <div id="bla" />
        </div>
    </div>
</template>

<script>
    const axios = require('axios');
    import DatasetOverTime from "./components/stats/DatasetOverTime";
    import DatasetSizeRanges from "./components/stats/DatasetSizeRanges";

    export default {
        name: "StatsApp",
        components: { DatasetOverTime, DatasetSizeRanges },
    }
</script>

<style scoped>

</style>
