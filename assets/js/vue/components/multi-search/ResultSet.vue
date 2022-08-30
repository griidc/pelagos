<template>
  <div v-if="results.count > 0">
    <section class="section-content pt-3">
      <div class="row d-flex flex-row justify-content-center">
        <h5>
          Found {{ resultCount }} results
        </h5>
      </div>
    </section>
    <section class="section-content pb-2">
      <div class="row">
        <aside class="col-lg-3">
          <div class="card card-filter">
            <Facet :facet-info="results.facetInfo.researchGroupInfo" :facet-name="facetLabels.researchGroup" v-on="$listeners" :formValues="formValues"/>
          </div>
        </aside>
        <main class="col-lg-9 overflow-auto">
          <div v-for="thing in results.results" :key="thing.id">
            <InformationProductCard v-if="thing.friendlyName == 'Information Product'"
                                   :key="thing.id"
                                   :informationProduct="thing"/>

            <DatasetRow v-if="thing.friendlyName == 'Dataset'"
                      :key="thing.udi"
                      :datasetRowData="thing"/>
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
  components: { NoResults, InformationProductCard, Facet, DatasetRow },
  props: {
    results: {
      type: Object,
    },
    formValues: {
      type: Object,
    },
  },
  computed: {
    resultCount() {
      return this.results.count;
    },
  },
  data() {
    return {
      facetLabels: {
        researchGroup: {
          label: templateSwitch.getProperty('researchGroup'),
          queryParam: 'researchGroup',
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
