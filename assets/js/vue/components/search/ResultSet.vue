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
            <div class="row d-flex flex-row justify-content-end page-controls">
                <b-pagination
                        v-model="currentPage"
                        :total-rows="rows"
                        :per-page="formValues.perPage"
                        class="justify-content-center pr-3 mr-3">
                </b-pagination>
                <div class="form-inline">
                  <div class="form-inline mx-2 mb-2 pr-2 pb-2">
                    <label for="sortBy" class="pr-2">Sort By: </label>
                    <b-form-select
                        name="sortBy"
                        v-model="sortBy"
                        :options="sortByOptions"></b-form-select>
                  </div>
                  <div class="form-inline mx-2 mb-2 pr-2 pb-2">
                    <label for="perPageResults" class="pr-2">Per Page: </label>
                    <b-form-select
                        name="perPageResults"
                        v-model="perPage"
                        :options="perPageOptions"></b-form-select>
                  </div>
              </div>
            </div>

            <div class="row">
                <aside class="col-lg-3">
                    <div class="card card-filter">
                        <Facet :facet-info="results.facetInfo.statusInfo" :facet-name="facetLabels.status" v-on="$listeners" :formValues="formValues"/>
                        <Facet :facet-info="results.facetInfo.fundingCycleInfo" :facet-name="facetLabels.fundingCycle" v-on="$listeners" :formValues="formValues" v-show="showFundingCycleFacet"/>
                        <Facet :facet-info="results.facetInfo.funderInfo" :facet-name="facetLabels.funder" v-on="$listeners" :formValues="formValues" v-show="showFunderFacet"/>
                        <Facet :facet-info="results.facetInfo.projectDirectorInfo" :facet-name="facetLabels.projectDirector" v-on="$listeners" :formValues="formValues" v-show="showProjectDirectorFacet"/>
                        <Facet :facet-info="results.facetInfo.researchGroupsInfo" :facet-name="facetLabels.researchGroup" v-on="$listeners" :formValues="formValues"/>
                    </div>
                </aside>
                <main class="col-lg-9 overflow-auto">
                    <DatasetRow :datasetRowData="resultRow" v-for="resultRow in results.resultData" v-bind:key="resultRow.udi"/>
                </main>
            </div>
            <div class="row d-flex flex-row justify-content-between mb-2">
                <div class="empty-div"></div>
                <b-pagination
                        v-model="currentPage"
                        :total-rows="rows"
                        :per-page="formValues.perPage"
                        class="justify-content-center pl-5 ml-5">
                </b-pagination>
                <div class="form-inline mx-2 mb-2 pr-2 pb-2">
                    <label for="perPageResults" class="pr-2">Per Page: </label>
                    <b-form-select
                            name="perPageResults"
                            v-model="perPage"
                            :options="perPageOptions"></b-form-select>
                </div>
            </div>
        </section>
    </div>
    <div v-else>
      <NoResults/>
    </div>
</template>

<script>
import Facet from '@/vue/components/search/Facet';
import DatasetRow from '@/vue/components/search/DatasetRow';
import templateSwitch from '@/vue/utils/template-switch';
import NoResults from '@/vue/components/info-search/NoResults';

export default {
  name: 'ResultSet',
  components: { DatasetRow, Facet, NoResults },
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
        status: {
          label: templateSwitch.getProperty('status'),
          queryParam: 'status',
        },
        fundingCycle: {
          label: templateSwitch.getProperty('fundingCycle'),
          queryParam: 'fundingCycle',
        },
        researchGroup: {
          label: templateSwitch.getProperty('researchGroup'),
          queryParam: 'researchGroup',
        },
        projectDirector: {
          label: 'Project Directors',
          queryParam: 'projectDirector',
        },
        funder: {
          label: templateSwitch.getProperty('funder'),
          queryParam: 'funder',
        },
      },
      currentPage: 1,
      perPage: this.formValues.perPage,
      sortBy: this.formValues.sortOrder,
      perPageOptions: [
        { value: 10, text: '10' },
        { value: 25, text: '25' },
        { value: 50, text: '50' },
        { value: 100, text: '100' },
      ],
      sortByOptions: [
        { value: 'default', text: 'Relevance' },
        { value: 'desc', text: 'Published Date (Desc)' },
        { value: 'asc', text: 'Published Date (Asc)' },
      ],
      showFundingCycleFacet: templateSwitch.getProperty('showFundingCycles'),
      showProjectDirectorFacet: templateSwitch.getProperty('showProjectDirector'),
      showFunderFacet: templateSwitch.getProperty('showFunderFacet'),
    };
  },
  computed: {
    rows() {
      return this.results.count;
    },
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
