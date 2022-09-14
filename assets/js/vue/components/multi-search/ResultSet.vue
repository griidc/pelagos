<template>
  <div v-if="results.count > 0">
    <section class="section-content pt-3">
      <div class="row d-flex flex-row justify-content-center">
        <h5>
          Found {{ results.count }} results
        </h5>
      </div>
    </section>
    <section class="section-content pb-2">
      <div class="row">
        <aside class="col-lg-3">
          <div class="card card-filter">
            <Facet :facet-info="results.facetInfo.dataTypeInfo" :facet-name="facetLabels.dataType" v-on="$listeners" :formValues="formValues"/>
            <Facet :facet-info="results.facetInfo.researchGroupInfo" :facet-name="facetLabels.researchGroup" v-on="$listeners" :formValues="formValues"/>
          </div>
        </aside>
        <main class="col-lg-9 overflow-auto">
          <div v-for="resultItem in results.results" :key="resultItem.id">
            <InformationProductCard v-if="resultItem.friendlyName == 'Information Product'"
                                   :key="resultItem.id"
                                   :informationProduct="resultItem"/>

            <DatasetRow v-if="resultItem.friendlyName == 'Dataset'"
                      :key="resultItem.udi"
                      :datasetRowData="resultItem"/>
          </div>
        </main>
      </div>
    </section>
  </div>
  <div v-else>
    <NoResults/>
  </div>
</template>

<script>
import InformationProductCard from '@/vue/components/information-product/InformationProductCard';
import DatasetRow from '@/vue/components/search/DatasetRow';
import NoResults from '@/vue/components/info-search/NoResults';
import Facet from '@/vue/components/search/Facet';
import templateSwitch from '@/vue/utils/template-switch';

export default {
  name: 'ResultSet',
  components: {
    NoResults,
    InformationProductCard,
    Facet,
    DatasetRow,
  },
  props: {
    results: {
      type: Object,
    },
    formValues: {
      type: Object,
    },
  },
  data() {
    return {
      facetLabels: {
        researchGroup: {
          label: templateSwitch.getProperty('researchGroup'),
          queryParam: 'researchGroup',
        },
        dataType: {
          label: 'Data Type',
          queryParam: 'dataType',
        },
      },
      showResults: false,
    };
  },
  mounted() {
    if (this.results.informationProducts) {
      this.showResults = true;
    }
  },
};
</script>

<style scoped>

</style>
