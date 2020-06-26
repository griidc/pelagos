<template>
    <div ref="formContainer">
        <section class="section-content bg pt-5">
            <div class="container">
                <div class="search-form">
                    <b-form id="searchForm" name="searchForm" method="get" @submit.prevent="onSubmit" @reset.prevent="onReset">
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
                                            <label class="pl-2 pr-2" for="field" >Search in Field</label>
                                            <b-form-select name="field" id="field" v-model="form.field" :options="fields">
                                            </b-form-select>
                                        </span>

                                    </div>
                                    <div class="col-lg collection-start-date">
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
                                    </div>
                                    <div class="col-lg collection-end-date">
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
                            </div>
                            <div class="col-lg-3 button-toolbar">
                                <button id="searchSubmit" type="submit" class="btn btn-alternate search-button">Search
                                    <i class="fa fa-search pl-2"></i></button>
                                <button type="reset" id="search-clear" class="btn btn-dark clear-button">Clear</button>
                                <div class="mt-3 pt-3 empty-button-div"></div>
                                <button type="button" id="map-search" class="btn btn-dark map-search-button" @click="dataDiscovery()">Map Search</button>
                            </div>
                        </div>
                    </b-form>
                </div>
            </div>
        </section>
        <ResultSet
                v-if="showResults"
                :results="resultSet"
                @facetClicked="facetCheckBoxValues"
                @pagination="changePageNo"
                @noOfResults="changeNoOfResults"
                :formValues="form"/>
        <section class="section-content pt-3 bg" v-else>
            <div class="container" />
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
            changeNoOfResults: function (noOfResults) {
                this.form.perPage = noOfResults;
                this.onSubmit();
            },
            detectHashChange: function () {
                this.route = window.location.hash;
                this.submitted = false;
            },
            dataDiscovery: function () {
                window.location.href = Routing.generate("pelagos_app_ui_datadiscovery_default");
            }
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
                        const urlHashSplit = decodeURI(this.route).split("#")[1].split("&").map(value => value.split("="));
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
                researchGroup: '',
                perPage: 10
        }
    }
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

    @media (max-width: 992px) {
        .search-form {
            .button-toolbar{
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

    @media (min-width: 992px) {
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
</style>
