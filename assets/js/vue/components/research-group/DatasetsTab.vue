<template>
    <b-card-text class="vh-100">
        <DatasetRow :datasetRowData="dataset" v-for="dataset in datasets" v-bind:key="dataset.udi"/>
    </b-card-text>
</template>

<script>
import DatasetRow from '../search/DatasetRow';

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
