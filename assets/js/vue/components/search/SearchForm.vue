<template>
    <div ref="formContainer">
        <section class="section-content bg pt-5">
            <div class="container">
                <div class="card card-header">
                    <b-form id="searchForm" name="searchForm" method="get" @submit.prevent="onSubmit" @reset.prevent="onReset">
                        <div class="row">
                            <div class="col-sm-9">
                                <b-form-input type="search"
                                              name="query"
                                              class="form-control"
                                              placeholder="Search.."
                                              id="searchBox"
                                              v-model="form.query">
                                </b-form-input>
                                <input type="hidden" id="pageNo" name="page" v-model="form.page">
                            </div>
                            <div class="col-sm-3 btn-toolbar">
                                <button id="searchSubmit" type="submit" class="btn btn-primary mx-2 w-50">Search
                                    <i class="fa fa-search pl-2"></i></button>
                                <button type="reset" id="search-clear" class="btn btn-dark mx-2 w-25">Clear</button>
                            </div>
                        </div>
                        <div class="pt-3 mt-3 d-flex flex-row justify-content-around">
                            <div class="pl-5 pt-1">
                            <span>
                                <b-form-select name="field" id="field" v-model="form.field" :options="fields">
                                </b-form-select>
                            </span>
                            </div>
                            <div class="form-inline">
                            <span class="input-group">
                                <label for="collectionStartDate" class="pl-2 pr-2">From</label>
                                <b-form-datepicker type="text"
                                       class="pr-2 form-control"
                                       id="collectionStartDate"
                                       name="collectionStartDate"
                                       placeholder="yyyy-mm-dd"
                                       v-model="form.collectionStartDate">
                                </b-form-datepicker>
                            </span>
                                <span class="input-group">
                                <label for="collectionEndDate" class="pr-2 pl-3">To</label>
                                <b-form-datepicker
                                        type="text"
                                        id="collectionEndDate"
                                        class="form-control date-input"
                                        name="collectionEndDate"
                                        placeholder="yyyy-mm-dd"
                                        v-model="form.collectionEndDate">
                                </b-form-datepicker>
                            </span>
                            </div>
                        </div>
                    </b-form>
                </div>
            </div>
        </section>
        <ResultSet v-if="showResults" :results="resultSet" @facetClicked="facetCheckBoxValues" @pagination="changePageNo" :formValues="form"/>
        <section class="section-content pt-3 bg" v-else>
            <div class="container">
                <article class="card card-product">
                    <div class="card-body text-center">
                        <h5 class="card-title">
                            GRIIDC Data Sets
                        </h5>
                        <p class="card-text">
                            Choose from thousands of scientific datasets from various fields
                            including oceanography, biology, ecology, chemistry, social science,
                            and others. Datasets are primarily focused on the Deepwater Horizon
                            oil spill in the Gulf of Mexico; however, some datasets are related
                            to other topics and locations around the world.
                        </p>
                    </div>
                </article>
            </div>
        </section>
    </div>
</template>

<script>
    const axios = require('axios');
    import ResultSet from "./ResultSet";
    export default {
        name: "SearchForm",
        components: { ResultSet },
        data: function() {
            return {
                searchFormRoute: Routing.generate('pelagos_app_ui_searchpage_results'),
                form: initialFormValues(),
                fields: [
                    {text: '-- All --', value: '' },
                    {text: 'Title', value: 'title'},
                    {text: 'Abstract', value: 'abstract'},
                    {text: 'Author', value: 'datasetSubmission.authors'},
                    {text: 'Theme Keywords', value: 'datasetSubmission.themeKeywords'}],
                showResults: false,
                noResults: false,
                resultSet: Object,
                route: window.location.hash,
                submitted: false,
            }
        },
        methods: {
            onSubmit: function () {
                const searchQuery = Object.keys(this.form).map(key => key + '=' + this.form[key]).join('&');
                let loader = null;
                let thisComponent = this;
                const axiosInstance = axios.create({});
                axiosInstance.interceptors.request.use(function (config) {
                    loader = thisComponent.$loading.show({
                        container: thisComponent.$refs.formContainer,
                        loader: 'bars',
                        color: '#007bff',
                    });
                    return config;
                }, function (error) {
                    return Promise.reject(error);
                });

                function hideLoader(){
                    loader && loader.hide();
                    loader = null;
                }

                axiosInstance.interceptors.response.use(function (response) {
                    hideLoader();
                    return response;
                }, function (error) {
                    hideLoader();
                    return Promise.reject(error);
                });

                axiosInstance
                    .get(Routing.generate('pelagos_app_ui_searchpage_results') + "?" + searchQuery)
                    .then(response => {
                        this.resultSet = response.data;
                        this.showResults = true;
                        window.location.hash = searchQuery;
                        this.route = window.location.hash;
                        this.submitted = true;
                    });
            },
            onReset: function () {
                this.form = initialFormValues();
                this.showResults = false;
                this.noResults = false;
                window.location.hash = '';
            },
            facetCheckBoxValues: function (value) {
                let facetArray = value.split("=");
                this.form[facetArray[0]] = facetArray[1];
                this.onSubmit();
            },
            changePageNo: function (newPageNo) {
                this.form.page = newPageNo;
                this.onSubmit();
            },
            detectHashChange: function () {
                this.route = window.location.hash;
                this.submitted = false;
            },
        },
        mounted() {
            if (this.route) {
                const urlHashSplit = decodeURI(this.route).split("#")[1].split("&").map(value => value.split("="));
                this.form = Object.fromEntries(urlHashSplit);
                this.onSubmit();
            }
            window.addEventListener('hashchange', this.detectHashChange);
        },
        watch: {
            route: function (value) {
                if (!this.submitted) {
                    if (this.route) {
                        const urlHashSplit = this.route.split("#")[1].split("&").map(value => value.split("="));
                        this.form = Object.fromEntries(urlHashSplit);
                        this.onSubmit();
                    } else {
                        this.onReset();
                    }
                }
            }
        }
    }

    function initialFormValues() {
        return {
                query: '',
                page: 1,
                field: '',
                collectionStartDate: '',
                collectionEndDate: '',
                status: '',
                fundingOrg: '',
                researchGroup: ''
        }
    }
</script>

<style scoped>

</style>