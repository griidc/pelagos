<template>
    <div v-if="results.count > 0">
        <section class="section-content pt-3">
            <div class="row d-flex flex-row justify-content-center">
                <h5>
                    Found {{ results.count }} results for: "{{ results.formValues.query }}"
                </h5>
            </div>
        </section>
        <section class="section-content pb-2">
            <div class="row d-flex flex-row justify-content-between">
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

            <div class="row">
                <aside class="col-lg-3">
                    <div class="card card-filter">
                        <Facet :facet-info="results.facetInfo.statusInfo" :facet-name="facetLabels.status" v-on="$listeners" :formValues="formValues"/>
                        <Facet :facet-info="results.facetInfo.fundingCycleInfo" :facet-name="facetLabels.fundingCycle" v-on="$listeners" :formValues="formValues" v-if="showFundingCycleFacet"/>
                        <Facet :facet-info="results.facetInfo.fundingOrgInfo" :facet-name="facetLabels.fundingOrg" v-on="$listeners" :formValues="formValues" v-else/>
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
        <section class="section-content pt-5">
            <div class="row d-flex flex-row justify-content-center">
                <h3>
                    No results found!
                </h3>
            </div>
        </section>
    </div>
</template>

<script>
import Facet from './Facet';
import DatasetRow from './DatasetRow';
import templateSwitch from '../../utils/template-switch.js';

export default {
  name: 'ResultSet',
  components: { DatasetRow, Facet },
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
        fundingOrg: {
          label: templateSwitch.getProperty('fundingOrg'),
          queryParam: 'fundingOrg',
        },
        researchGroup: {
          label: templateSwitch.getProperty('researchGroup'),
          queryParam: 'researchGroup',
        },
        projectDirector: {
          label: 'Project Directors',
          queryParam: 'projectDirector',
        },
      },
      currentPage: 1,
      perPage: this.formValues.perPage,
      perPageOptions: [
        { value: 10, text: '10' },
        { value: 25, text: '25' },
        { value: 50, text: '50' },
        { value: 100, text: '100' },
      ],
      showFundingCycleFacet: templateSwitch.getProperty('showFundingCycles'),
      showProjectDirectorFacet: templateSwitch.getProperty('showProjectDirector'),
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
  }
</style>
