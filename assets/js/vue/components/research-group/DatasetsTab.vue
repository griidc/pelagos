<template>
    <b-card-text>
        <DatasetRow :datasetRowData="dataset" v-for="dataset in datasets" v-bind:key="dataset.udi" v-show="dataset.dif.status === 2"/>
    </b-card-text>
</template>

<script>
import DatasetRow from '@/vue/components/search/DatasetRow';

export default {
  name: 'DatasetsTab',
  components: { DatasetRow },
  props: {
    datasets: {},
  },
  data() {
    return {
      datasetsRetrievedInfo: [],
    };
  },
};
</script>

<style scoped>

</style>
