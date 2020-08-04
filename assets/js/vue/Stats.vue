<template>
    <div class="row">
        <div class="col-xl-12">
            <dataset-over-time />
        </div>
        <div class="col-xl-12">
            <dataset-size-ranges />
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
