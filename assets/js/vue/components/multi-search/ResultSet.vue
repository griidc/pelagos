<template>
  <div v-if="results.count > 0">
    <section class="section-content pt-3">
      <div class="row d-flex flex-row justify-content-center">
        <h5>Found {{ results.count }} results</h5>
      </div>
    </section>
    <section class="section-content pb-2">
      <div class="row d-flex flex-row justify-content-end page-controls">
        <b-pagination
          v-model="currentPage"
          :total-rows="results.count"
          :per-page="formValues.perPage"
          class="justify-content-center pr-3 mr-3"
        >
        </b-pagination>
        <div class="form-inline">
          <div class="form-inline mx-2 mb-2 pr-2 pb-2">
            <label for="sortBy" class="pr-2">Sort By: </label>
            <b-form-select
              name="sortBy"
              v-model="sortBy"
              :options="sortByOptions">
            </b-form-select>
          </div>
          <div class="form-inline mx-2 mb-2 pr-2 pb-2">
            <label for="perPageResults" class="pr-2">Per Page: </label>
            <b-form-select
              name="perPageResults"
              v-model="perPage"
              :options="perPageOptions"
            ></b-form-select>
          </div>
        </div>
      </div>
      <div class="row">
        <aside class="col-lg-3">
          <div class="card card-filter">
            <Facet
              v-for="(facetInfo, name, index) in results.facetInfo"
              v-bind:key="index"
              :facet-info="facetInfo"
              :facet-name="getFacetLabel(name)"
              v-on="$listeners"
              :formValues="formValues"
            />
          </div>
        </aside>
        <main class="col-lg-9 overflow-auto">
          <div v-for="resultItem in results.results" :key="resultItem.id">
            <InformationProductCard
              v-if="resultItem.friendlyName == 'Information Product'"
              :key="resultItem.id"
              :informationProduct="resultItem"
            />

            <DatasetRow
              v-if="resultItem.friendlyName == 'Dataset'"
              :key="resultItem.udi"
              :datasetRowData="resultItem"
            />
          </div>
        </main>
      </div>
      <div class="row d-flex flex-row justify-content-between mb-2">
        <div class="empty-div"></div>
        <b-pagination
          v-model="currentPage"
          :total-rows="results.count"
          :per-page="formValues.perPage"
          class="justify-content-center pr-3 mr-3"
        >
        </b-pagination>
        <div class="form-inline mx-2 mb-2 pr-2 pb-2">
          <label for="perPageResults" class="pr-2">Per Page: </label>
          <b-form-select
            name="perPageResults"
            v-model="perPage"
            :options="perPageOptions"
          ></b-form-select>
        </div>
      </div>
    </section>
  </div>
  <div v-else>
    <NoResults />
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
        researchGroupInfo: {
          label: templateSwitch.getProperty('researchGroup'),
          queryParam: 'researchGroup',
          expanded: templateSwitch.getProperty('researchGroupExpanded'),
        },
        fundersInfo: {
          label: templateSwitch.getProperty('funder'),
          queryParam: 'funder',
          expanded: templateSwitch.getProperty('fundingOrgExpanded'),
        },
        dataTypeInfo: {
          label: 'Type',
          queryParam: 'dataType',
          expanded: templateSwitch.getProperty('typeExpanded'),
        },
        statusInfo: {
          label: templateSwitch.getProperty('status'),
          queryParam: 'status',
          expanded: templateSwitch.getProperty('statusExpanded'),
        },
        tagsInfo: {
          label: templateSwitch.getProperty('tags'),
          queryParam: 'tags',
          expanded: templateSwitch.getProperty('tagsExpanded'),
        },
      },
      showResults: false,
      currentPage: 1,
      perPage: this.formValues.perPage,
      perPageOptions: [
        { value: 10, text: '10' },
        { value: 25, text: '25' },
        { value: 50, text: '50' },
        { value: 100, text: '100' },
      ],
      sortBy: this.formValues.sortOrder,
      sortByOptions: [
        { value: 'default', text: 'Relevance' },
        { value: 'desc', text: 'Published Date (Desc)' },
        { value: 'asc', text: 'Published Date (Asc)' },
      ],
    };
  },
  mounted() {
    if (this.results) {
      this.showResults = true;
    }
  },
  watch: {
    currentPage(value) {
      this.$emit('pagination', value);
    },
    perPage(value) {
      this.$emit('noOfResults', value);
    },
    sortBy(value) {
      this.$emit('sortOrder', value);
    },
  },
  methods: {
    getFacetLabel(facetName) {
      const tempFacetLabelObj = Object.entries(this.facetLabels);
      const facetLabels = new Map(tempFacetLabelObj);
      if (facetLabels.has(facetName)) {
        return facetLabels.get(facetName);
      }
      return '';
    },
    toggleCollapses(ids) {
      ids.forEach((id) => {
        this.$root.$emit('bv::toggle::collapse', id);
      });
    },
  },
};
</script>

<style scoped lang="scss">
  @media (max-width: 1092px) {
    .page-controls.d-flex.flex-row {
      flex-direction: column !important;
    }
  }
</style>
