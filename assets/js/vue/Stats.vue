<template>
    <div class="dx-viewport">
        <div class="row w-75 mx-auto">
            <div class="col-xl-12 pb-5 pt-3">
                <repository-summary />
            </div>
            <div class="col-xl-12 pb-5">
                <dataset-over-time />
            </div>
            <div class="col-xl-12 pb-5">
                <dataset-size-ranges />
            </div>
        </div>
    </div>
</template>

<script>
import DatasetOverTime from '@/vue/components/stats/DatasetOverTime';
import DatasetSizeRanges from '@/vue/components/stats/DatasetSizeRanges';
import RepositorySummary from '@/vue/components/stats/RepositorySummary.vue';

export default {
  name: 'StatsApp',
  components: { DatasetOverTime, DatasetSizeRanges, RepositorySummary },
};
</script>

<style scoped>

</style>
