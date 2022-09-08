<template>
  <div v-if="results.informationProducts.length > 0">
    <section class="section-content pt-3">
      <div class="row d-flex flex-row justify-content-center">
        <h5>
          Found {{ resultCount }} results
        </h5>
      </div>
    </section>
    <section class="section-content pb-2">
      <div class="row d-flex flex-row justify-content-center page-controls">
        <b-pagination
            v-model="currentPage"
            :total-rows="resultCount"
            :per-page="formValues.perPage"
            class="justify-content-center pr-3 mr-3">
        </b-pagination>
      </div>

      <div class="row">
        <aside class="col-lg-3">
          <div class="card card-filter">
            <!-- Need response data to get facet data -->
            <Facet :facet-info="results.facetInfo.productTypeDescriptorInfo" :facet-name="facetLabels.productTypeDesc" v-on="$listeners" :formValues="formValues"/>
            <Facet :facet-info="results.facetInfo.digitalResourceTypeDescriptorsInfo" :facet-name="facetLabels.digitalTypeDesc" v-on="$listeners" :formValues="formValues"/>
            <Facet :facet-info="results.facetInfo.researchGroupInfo" :facet-name="facetLabels.researchGroup" v-on="$listeners" :formValues="formValues"/>
          </div>
        </aside>
        <main class="col-lg-9 overflow-auto">
          <InformationProductCard v-for="informationProduct in results.informationProducts"
                                  :key="informationProduct.id" v-show="informationProduct.published"
                                  :informationProduct="informationProduct"/>
        </main>
      </div>
      <div class="row d-flex flex-row justify-content-center page-controls">
        <b-pagination
            v-model="currentPage"
            :total-rows="resultCount"
            :per-page="formValues.perPage"
            class="justify-content-center pr-3 mr-3">
        </b-pagination>
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
      const informationProducts = this.results.informationProducts ?? [];
      informationProducts.forEach((informationProduct) => {
        if (informationProduct.published) {
          resultCount += 1;
        }
      });
      return resultCount;
    },
  },
  data() {
    return {
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
      showResults: false,
      currentPage: 1,
    };
  },
  mounted() {
    if (this.results.informationProducts) {
      this.showResults = true;
    }
  },
  watch: {
    currentPage(value) {
      this.$emit('pagination', value);
    },
  }
};
</script>

<style scoped lang="scss">
.col-lg-3 {
  padding-right: 7px !important;
}

.col-lg-9 {
  padding-left: 7px !important;
}

@media (max-width: 1092px) {
  .col-lg-3 {
    padding-right: 15px !important;
  }

  .col-lg-9 {
    padding-left: 15px !important;
    margin-top: 10px;
  }

  .page-controls.d-flex.flex-row {
    flex-direction: column !important;
  }
}
</style>
