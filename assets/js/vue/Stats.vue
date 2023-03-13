<template>
    <div class="dx-viewport">
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
    </div>
</template>

<script>
import DatasetOverTime from '@/vue/components/stats/DatasetOverTime';
import DatasetSizeRanges from '@/vue/components/stats/DatasetSizeRanges';
import RepositorySummary from '@/vue/components/stats/RepositorySummary';

export default {
  name: 'StatsApp',
  components: { DatasetOverTime, DatasetSizeRanges, RepositorySummary },
};
</script>

<style scoped>

</style>
