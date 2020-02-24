<template>
    <section class="section-content bg pt-5">
        <div class="container">
            <div class="card card-header">
                <b-form id="searchForm" name="searchForm" method="get" @submit.prevent="onSubmit" v-if="show">
                    <div class="row">
                        <div class="col-sm-9">
                            <b-form-input type="search"
                                   name="query"
                                   class="form-control"
                                   placeholder="Search.."
                                   id="searchBox"
                                   required
                                   v-model="form.query">
                            </b-form-input>
                            <input type="hidden" id="pageNo" name="page" v-model="form.pageNo">
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
                                <input type="text"
                                       class="pr-2 form-control"
                                       id="collectionStartDate"
                                       name="collectionStartDate"
                                       placeholder="yyyy-mm-dd"
                                       v-model="form.collectionStartDate">
                                {{form.collectionStartDate}}
                                <span class="input-group-append">
                                     <button class="btn btn-primary" type="button" id="collection-start-btn">
                                        <i class="far fa-calendar-alt"></i>
                                    </button>
                                </span>
                            </span>
                            <span class="input-group">
                                <label for="collectionEndDate" class="pr-2 pl-3">To</label>
                                <input
                                        type="text"
                                        class="form-control date-input"
                                        id="collectionEndDate"
                                        name="collectionEndDate"
                                        placeholder="yyyy-mm-dd"
                                        v-model="form.collectionEndDate">
                                {{form.collectionEndDate}}
                                <span class="input-group-append">
                                    <button class="btn btn-primary date-input" type="button" id="collection-end-btn">
                                        <i class="far fa-calendar-alt"></i>
                                    </button>
                                </span>
                            </span>
                        </div>
                    </div>
                </b-form>
            </div>
        </div>
    </section>
</template>

<script>
    const axios = require('axios');
    export default {
        name: "SearchForm",
        data: function() {
            return {
                searchFormRoute: Routing.generate('pelagos_app_ui_searchpage_results'),
                form: {
                    query : '',
                    pageNo : 1,
                    field : '',
                    collectionStartDate: '',
                    collectionEndDate: '',
                },
                fields: [{ text: '-- All --', value: null } , 'Title', 'Abstract', 'Author', 'Theme Keywords'],
                show: true
            }
        },
        methods: {
            onSubmit: function () {
                let searchQuery = Object.keys(this.form).map(key => key + '=' + this.form[key]).join('&');
                console.log(searchQuery);
                axios
                    .get(Routing.generate('pelagos_app_ui_searchpage_results') + "?" + searchQuery)
                    .then(response => (console.log(response.data)));
            }
        }
    }
</script>

<style scoped>

</style>