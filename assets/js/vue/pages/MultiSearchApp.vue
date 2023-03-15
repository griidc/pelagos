<template>
  <div ref="formContainer" class="bg">
    <div class="container">
      <section class="section-content pt-2">
        <div class="search-form">
          <b-form
            id="searchForm"
            name="searchForm"
            method="get"
            @submit.prevent="onSubmit"
            @reset.prevent="onReset"
          >
            <div class="row">
              <div class="col-lg-9">
                <div class="row">
                  <div class="col-lg">
                    <b-form-input
                      type="search"
                      name="query"
                      class="form-control"
                      placeholder="Search.."
                      id="searchBox"
                      v-model="form.queryString"
                    >
                    </b-form-input>
                  </div>
                </div>
                <div class="row mt-3 form-group form-inline pt-3 d-flex justify-content-between">
                  <div class="col-lg search-field-options">
                    <span class="input-group" v-tooltip="{
                            content: 'Search only in selected field',
                            placement:'top'}">
                        <label class="pr-1" for="field">Field Type</label>
                        <b-form-select name="field" id="field" v-model="form.field" :options="fields">
                        </b-form-select>
                    </span>
                  </div>
                  <div class="col-lg search-field-options">
                    <span class="input-group" v-tooltip="{
                          content: `Collection Date: date data were collected/generated.
                                    Published Date: date resource was published on GRIIDC.`,
                          placement:'top'}">
                      <label class="pr-1" for="field"
                        >Date Type</label
                      >
                      <b-form-select
                        name="dateType"
                        id="dateType"
                        v-model="form.dateType"
                        :options="dateTypeOptions"
                      >
                      </b-form-select>
                    </span>
                  </div>
                  <div class="col-lg range-start-date">
                    <span class="input-group">
                      <label for="rangeStartDate" class="pr-1">From</label>
                      <DxDateBox
                        :ref="rangeStartDateRef"
                        :element-attr="dateBoxAttributes"
                        id="rangeStartDate"
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
                  <div class="col-lg range-end-date">
                    <span class="input-group">
                      <label for="rangeEndDate" class="pr-1"
                        >To</label
                      >
                      <DxDateBox
                        :ref="rangeEndDateRef"
                        :element-attr="dateBoxAttributes"
                        id="rangeEndDate"
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
                <button
                  id="searchSubmit"
                  type="submit"
                  class="btn btn-alternate search-button"
                >
                  Search <i class="fa fa-search pl-2"></i>
                </button>
                <button
                  type="reset"
                  id="search-clear"
                  class="btn btn-dark clear-button"
                >
                  Clear
                </button>
                <div class="mt-3 pt-3 empty-button-div"></div>
                <button
                  type="button"
                  id="map-search"
                  class="btn btn-dark map-search-button"
                  @click="dataDiscovery()">Map Search
                </button>
              </div>
            </div>
          </b-form>
        </div>
      </section>
      <ResultSet
        v-if="showResults"
        :results="results"
        @facetClicked="facetCheckBoxValues"
        @pagination="changePageNo"
        @noOfResults="changeNoOfResults"
        :formValues="form"
        @sortOrder="changeSortOrder"
      />
    </div>
  </div>
</template>

<script>
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import DxDateBox from 'devextreme-vue/date-box';
import { getApi } from '@/vue/utils/axiosService';
import ResultSet from '@/vue/components/multi-search/ResultSet';

function initialFormValues() {
  return {
    queryString: '',
    researchGroup: '',
    page: 1,
    perPage: 10,
    fundingOrg: '',
    dataType: '',
    status: '',
    dateType: 'collectionDate',
    rangeStartDate: '',
    rangeEndDate: '',
    tags: '',
    sortOrder: 'default',
    field: '',
  };
}

const rangeStartDateRef = 'range-start-date';
const rangeEndDateRef = 'range-end-date';

export default {
  name: 'MultiSearchApp',
  components: { ResultSet, DxDateBox },
  data() {
    return {
      form: initialFormValues(),
      results: Object,
      showResults: false,
      dateTypeOptions: [
        { text: 'Collection', value: 'collectionDate' },
        { text: 'Published', value: 'publishedDate' },
      ],
      dateBoxAttributes: {
        class: 'datebox-font-family',
      },
      startDate: '',
      endDate: '',
      rangeStartDateRef,
      rangeEndDateRef,
      route: window.location.hash,
      submitted: false,
      fields: [
        { text: '-- All --', value: '' },
        { text: 'Title', value: 'title' },
        { text: 'Abstract', value: 'abstract' },
        { text: 'Author', value: 'datasetSubmission.authors' },
        { text: 'Theme Keywords', value: 'datasetSubmission.themeKeywords' }],
    };
  },
  methods: {
    init() {
      this.form = initialFormValues();
      this.detectHashChange();
      this.decodeHash();
      this.onSubmit();
    },
    onSubmit() {
      const searchQuery = Object.keys(this.form)
        .map((key) => `${key}=${this.form[key]}`)
        .join('&');
      getApi(
        // eslint-disable-next-line no-undef
        `${Routing.generate('app_multi_search_api')}?${searchQuery}`,
        { thisComponent: this, addLoading: true },
      ).then((response) => {
        this.results = response.data;
        this.showResults = true;
        window.location.hash = searchQuery;
        this.route = window.location.hash;
        this.submitted = true;
      });
    },
    onStartDateChanged(event) {
      if (event.value instanceof Date) {
        this.form.rangeStartDate = event.value.toLocaleDateString();
      } else if (event.value) {
        this.form.rangeStartDate = event.value;
      } else {
        this.form.rangeStartDate = '';
      }
    },
    onEndDateChanged(event) {
      if (event.value instanceof Date) {
        this.form.rangeEndDate = event.value.toLocaleDateString();
      } else if (event.value) {
        this.form.rangeEndDate = event.value;
      } else {
        this.form.rangeEndDate = '';
      }
    },
    onReset() {
      this.$refs[rangeStartDateRef].instance.reset();
      this.$refs[rangeEndDateRef].instance.reset();
      this.form = initialFormValues();
      this.detectHashChange();
      this.showResults = false;
      this.onSubmit();
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
    detectHashChange() {
      this.route = window.location.hash;
      this.submitted = false;
    },
    decodeHash() {
      if (this.route) {
        const urlHashSplit = decodeURI(this.route).split('#')[1].split('&').map((value) => value.split('='));
        this.form = Object.fromEntries(urlHashSplit);
      }
    },
    changeSortOrder(sortOrder) {
      this.form.sortOrder = sortOrder;
      this.onSubmit();
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
          this.decodeHash();
          this.onSubmit();
        } else {
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
