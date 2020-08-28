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
    import DatasetOverTime from "./components/stats/DatasetOverTime";
    import DatasetSizeRanges from "./components/stats/DatasetSizeRanges";
    import RepositorySummary from "./components/stats/RepositorySummary";

    export default {
        name: "StatsApp",
        components: { DatasetOverTime, DatasetSizeRanges, RepositorySummary },
    }
</script>

<style scoped>

</style>
