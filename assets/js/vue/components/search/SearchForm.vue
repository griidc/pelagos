<template>
    <div ref="formContainer" class="bg">
        <div class="container">
            <section class="section-content pt-2">
                <div class="search-form">
                    <b-form id="searchForm" name="searchForm" method="get" @submit.prevent="onSubmit"
                            @reset.prevent="onReset">
                        <div class="row">
                            <div class="col-lg-9">
                                <div class="row">
                                    <div class="col-lg">
                                        <b-form-input type="search"
                                                      name="query"
                                                      class="form-control"
                                                      placeholder="Search.."
                                                      id="searchBox"
                                                      v-model="form.query">
                                        </b-form-input>
                                        <input type="hidden" id="pageNo" name="page" v-model="form.page">
                                    </div>
                                </div>
                                <div class="row mt-3 form-group form-inline pt-3">
                                    <div class="col-lg search-field-options">
                                    <span class="input-group" v-tooltip="{
                                            content: 'Search only in selected field',
                                            placement:'top'}">
                                        <label class="pl-2 pr-2" for="field">Search in Field</label>
                                        <b-form-select name="field" id="field" v-model="form.field" :options="fields">
                                        </b-form-select>
                                    </span>

                                    </div>
                                    <div class="col-lg collection-start-date">
                                    <span class="input-group" v-tooltip="{
                                            content: 'Search by Data Acquisition Date',
                                            placement:'top'}">
                                        <label for="collectionStartDate" class="pr-2">From</label>
                                        <DxDateBox
                                          :ref="collectionStartDateRef"
                                          :element-attr="dateBoxAttributes"
                                          id="collectionStartDate"
                                          :show-clear-button="true"
                                          :use-mask-behavior="true"
                                          :value="startDate"
                                          placeholder="yyyy-mm-dd"
                                          display-format="yyyy-MM-dd"
                                          width="80%"
                                          type="date"
                                          @value-changed="onStartDateChanged"
                                        />
                                    </span>
                                    </div>
                                    <div class="col-lg collection-end-date">
                                    <span class="input-group" v-tooltip="{
                                            content: 'Search by Data Acquisition Date',
                                            placement:'top'}">
                                        <label for="collectionEndDate" class="pr-2 pl-3">To</label>
                                        <DxDateBox
                                          :ref="collectionEndDateRef"
                                          :element-attr="dateBoxAttributes"
                                          id="collectionEndDate"
                                          :show-clear-button="true"
                                          :use-mask-behavior="true"
                                          :value="endDate"
                                          placeholder="yyyy-mm-dd"
                                          display-format="yyyy-MM-dd"
                                          width="80%"
                                          type="date"
                                          @value-changed="onEndDateChanged"
                                        />
                                    </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 button-toolbar">
                                <button id="searchSubmit" type="submit" class="btn btn-primary search-button">Search
                                    <i class="fa fa-search pl-2"></i></button>
                                <button type="reset" id="search-clear" class="btn btn-primary clear-button">Clear</button>
                                <div class="mt-3 pt-3 empty-button-div"></div>
                                <button type="button" id="map-search" class="btn btn-primary map-search-button"
                                        @click="dataDiscovery()">Map Search
                                </button>
                            </div>
                        </div>
                    </b-form>
                </div>
            </section>
            <ResultSet
                v-if="showResults"
                :results="resultSet"
                @facetClicked="facetCheckBoxValues"
                @pagination="changePageNo"
                @noOfResults="changeNoOfResults"
                @sortOrder="changeSortOrder"
                :formValues="form"/>
        </div>
    </div>
</template>

<script>
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import { getApi } from '@/vue/utils/axiosService';
import ResultSet from '@/vue/components/search/ResultSet';
import DxDateBox from 'devextreme-vue/date-box';

function initialFormValues() {
  return {
    query: '',
    page: 1,
    field: '',
    collectionStartDate: '',
    collectionEndDate: '',
    status: '',
    fundingOrg: '',
    researchGroup: '',
    fundingCycle: '',
    perPage: 10,
    projectDirector: '',
    sortOrder: 'default',
    funder: '',
  };
}

const collectionStartDateRef = 'collection-start-date';
const collectionEndDateRef = 'collection-end-date';

export default {
  name: 'SearchForm',
  components: { ResultSet, DxDateBox },
  data() {
    return {
      // eslint-disable-next-line no-undef
      searchFormRoute: Routing.generate('pelagos_app_ui_searchpage_results'),
      form: initialFormValues(),
      startDate: '',
      endDate: '',
      collectionStartDateRef,
      collectionEndDateRef,
      fields: [
        { text: '-- All --', value: '' },
        { text: 'Title', value: 'title' },
        { text: 'Abstract', value: 'abstract' },
        { text: 'Author', value: 'datasetSubmission.authors' },
        { text: 'Theme Keywords', value: 'datasetSubmission.themeKeywords' }],
      showResults: false,
      noResults: false,
      resultSet: Object,
      route: window.location.hash,
      submitted: false,
      requestActive: false,
      dateBoxAttributes: {
        class: 'datebox-font-family',
      },
    };
  },
  methods: {
    init() {
      if (this.route) {
        const urlHashSplit = decodeURI(this.route).split('#')[1].split('&').map((value) => value.split('='));
        this.form = Object.fromEntries(urlHashSplit);
        this.startDate = this.form.collectionStartDate;
        this.endDate = this.form.collectionEndDate;
      }
      this.onSubmit();
      window.addEventListener('hashchange', this.detectHashChange);
    },
    onStartDateChanged(event) {
      if (event.value instanceof Date) {
        this.form.collectionStartDate = event.value.toLocaleDateString();
      } else if (event.value) {
        this.form.collectionStartDate = event.value;
      } else {
        this.form.collectionStartDate = '';
      }
    },
    onEndDateChanged(event) {
      if (event.value instanceof Date) {
        this.form.collectionEndDate = event.value.toLocaleDateString();
      } else if (event.value) {
        this.form.collectionEndDate = event.value;
      } else {
        this.form.collectionEndDate = '';
      }
    },
    onSubmit() {
      const searchQuery = Object.keys(this.form).map((key) => `${key}=${this.form[key]}`).join('&');
      if (this.requestActive) {
        this.$nextTick();
      }
      this.requestActive = true;
      getApi(
        // eslint-disable-next-line no-undef
        `${Routing.generate('pelagos_app_ui_searchpage_results')}?${searchQuery}`,
        { thisComponent: this, addLoading: true },
      ).then((response) => {
        this.resultSet = response.data;
        this.showResults = true;
        window.location.hash = searchQuery;
        this.route = window.location.hash;
        this.submitted = true;
        this.requestActive = false;
      });
    },
    onReset() {
      this.form = initialFormValues();
      this.showResults = false;
      this.noResults = false;
      this.startDate = '';
      this.endDate = '';
      window.location.hash = '';
      this.$refs[collectionStartDateRef].instance.reset();
      this.$refs[collectionEndDateRef].instance.reset();
      this.init();
    },
    facetCheckBoxValues(value) {
      const facetArray = value.split('=');
      // eslint-disable-next-line prefer-destructuring
      this.form[facetArray[0]] = facetArray[1];
      this.onSubmit();
    },
    changePageNo(newPageNo) {
      this.form.page = newPageNo;
      this.onSubmit();
    },
    changeNoOfResults(noOfResults) {
      this.form.perPage = noOfResults;
      this.onSubmit();
    },
    changeSortOrder(sortOrder) {
      this.form.sortOrder = sortOrder;
      this.onSubmit();
    },
    detectHashChange() {
      this.route = window.location.hash;
      this.submitted = false;
    },
    dataDiscovery() {
      // eslint-disable-next-line no-undef
      window.location.href = Routing.generate('pelagos_app_ui_datadiscovery_default');
    },
  },
  mounted() {
    this.init();
  },
  watch: {
    route() {
      if (!this.submitted) {
        if (this.route) {
          const urlHashSplit = decodeURI(this.route).split('#')[1].split('&').map((value) => value.split('='));
          this.form = Object.fromEntries(urlHashSplit);
          this.onSubmit();
        } else {
          this.clearForm = false;
          this.onReset();
        }
      }
    },
  },
};
</script>

<style scoped lang="scss">
.search-form {
    padding: 0.75rem 1.25rem;
    margin-bottom: 0;
    background-color: rgba(0, 0, 0, 0.03);
    border-radius: calc(0.25rem - 1px) calc(0.25rem - 1px) 0 0;
    background-clip: border-box;
    border: 1px solid rgba(0, 0, 0, 0.125);
}

@media (max-width: 1092px) {
    .search-form {
        .button-toolbar {
            margin-top: 1rem !important;
            padding-top: 1rem !important;

            .search-button {
                width: 50%;
            }

            .clear-button {
                width: 49%;
            }

            .map-search-button {
                margin-top: 1rem;
                width: 100% !important;
                margin-left: 0 !important;
            }

            .empty-button-div {
                margin-top: 0 !important;
            }
        }

        .search-field-options {
            margin-bottom: 0.5rem !important;
            padding-bottom: 0.5rem !important;

            select {
                width: 100% !important;
            }
        }

        .collection-start-date {
            margin-bottom: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }
    }
}

@media (min-width: 1092px) {
    .search-form {
        .button-toolbar {
            .search-button {
                margin-right: 0.5rem;
                width: 50%;
            }

            .clear-button {
                margin-right: 0.5rem;
                margin-left: 0.5rem;
                width: 30%;
            }

            .map-search-button {
                margin-left: 4.1rem;
            }
        }
    }
}

.datebox-font-family {
  font-family: var(--main-fonts);
  font-size: 16px;
}
</style>
