<template>
  <div v-if="results.length > 0">
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
            <!-- Need response data to get facet data -->
<!--            <Facet :facet-info="results.facetInfo.productTypeDesc" :facet-name="facetLabels.productTypeDesc" v-on="$listeners" :formValues="formValues"/>-->
<!--            <Facet :facet-info="results.facetInfo.digitalTypeDesc" :facet-name="facetLabels.digitalTypeDesc" v-on="$listeners" :formValues="formValues"/>-->
           <Facet :facet-info="results.facetInfo.researchGroupsInfo" :facet-name="facetLabels.researchGroup" v-on="$listeners" :formValues="formValues"/>
          </div>
        </aside>
        <main class="col-lg-9 overflow-auto">
          <InformationProductCard v-for="informationProduct in results"
                                  :key="informationProduct.id" v-show="informationProduct.published"
                                  :informationProduct="informationProduct"/>
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
import NoResults from '@/vue/components/info-search/NoResults';
import Facet from '@/vue/components/search/Facet';
import templateSwitch from '@/vue/utils/template-switch';

export default {
  name: 'ResultSet',
  components: { NoResults, InformationProductCard, Facet },
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
      let resultCount = 0;
      this.results.forEach((informationProduct) => {
        if (informationProduct.published) {
          resultCount += 1;
        }
      });
      return resultCount;
    },
  },
  methods: {
    facetLabels: {
      productTypeDesc: {
        label: templateSwitch.getProperty('productTypeDesc'),
        queryParam: 'productTypeDesc',
      },
      digitalTypeDesc: {
        label: templateSwitch.getProperty('digitalTypeDesc'),
        queryParam: 'digitalTypeDesc',
      },
      researchGroup: {
        label: templateSwitch.getProperty('researchGroup'),
        queryParam: 'researchGroup',
      },
    },
  },
};
</script>

<style scoped>

</style>
