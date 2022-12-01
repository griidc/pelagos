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
                                  v-model="form.queryString">
                    </b-form-input>
                  </div>
                </div>
              </div>
                <div class="col-lg-3 button-toolbar">
                  <button id="searchSubmit" type="submit" class="btn btn-alternate search-button">Search
                  <i class="fa fa-search pl-2"></i></button>
                  <button type="reset" id="search-clear" class="btn btn-dark clear-button">Clear</button>
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
          :formValues="form"/>
    </div>
  </div>
</template>

<script>

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
    tags: '',
  };
}

export default {
  name: 'MultiSearchApp',
  components: { ResultSet },
  data() {
    return {
      form: initialFormValues(),
      results: Object,
      showResults: false,
      route: window.location.hash,
      submitted: false,
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
      const searchQuery = Object.keys(this.form).map((key) => `${key}=${this.form[key]}`).join('&');
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
    onReset() {
      this.form = initialFormValues();
      this.detectHashChange();
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
      const urlHashSplit = decodeURI(this.route).split('#')[1].split('&').map((value) => value.split('='));
      this.form = Object.fromEntries(urlHashSplit);
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
    }
  }
}

.datebox-font-family {
  font-family: var(--main-fonts);
  font-size: 16px;
}
</style>
