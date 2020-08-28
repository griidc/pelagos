<template>
    <div class="row">
        <div class="col-xl-12">
            <repository-summary />
        </div>
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
    import RepositorySummary from "./components/stats/RepositorySummary";

    export default {
        name: "StatsApp",
        components: { DatasetOverTime, DatasetSizeRanges, RepositorySummary },
          methods: {
            getThemeColor: function(color) {
                return getComputedStyle(document.body).getPropertyValue("--" + color);
            },
          }
    }
</script>

<style scoped>

</style>
