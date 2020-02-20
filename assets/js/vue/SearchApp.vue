<template>
    <div class="bootstrap">
        <section class="section-content bg pt-5">
            <div class="container">
                <div class="card card-header">
                    <form id="searchForm" name="searchForm" method="get" v-bind:action="searchFormRoute">
                        <div class="row">
                            <div class="col-sm-9">
                                <input type="search"
                                       name="query"
                                       class="form-control"
                                       placeholder="Search.."
                                       id="searchBox"
                                       required>
                                <input type="hidden" id="pageNo" name="page" >
                            </div>
                            <div class="col-sm-3 btn-toolbar">
                                <button id="searchSubmit" type="submit" class="btn btn-primary mx-2 w-50">Search
                                    <i class="fa fa-search pl-2"></i></button>
                                <button type="button" id="search-clear" class="btn btn-dark mx-2 w-25">Clear</button>
                            </div>
                            <div class="pt-3 mt-3 d-flex flex-row justify-content-around">
                                <div class="pl-5 pt-1">
                                    <span class="form-options-input-group">
                                        <select class="form-options-input" name="field" id="field">
                                            <option>
                                                -- All --
                                            </option>
                                            <option>
                                                Title
                                            </option>
                                            <option>
                                                Abstract
                                            </option>
                                            <option>
                                                Author
                                            </option>
                                            <option>
                                                Theme Keywords
                                            </option>
                                        </select>
                                    </span>
                                </div>
                                <div class="form-inline">
                                    <span class="input-group">
                                        <label for="collectionStartDate" class="pl-2 pr-2">From</label>
                                        <input type="text"
                                               class="pr-2 form-control"
                                               id="collectionStartDate"
                                               name="collectionStartDate"
                                               placeholder="yyyy-mm-dd">
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
                                                placeholder="yyyy-mm-dd">
                                        <span class="input-group-append">
                                            <button class="btn btn-primary date-input" type="button" id="collection-end-btn">
                                                <i class="far fa-calendar-alt"></i>
                                            </button>
                                        </span>
                                     </span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    const routes = require('../../../public/js/fos_js_routes.json');
    import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
    Routing.setRoutingData(routes);
    export default {
        name: "SearchApp",
        data() {
            return {
                searchFormRoute: Routing.generate('pelagos_app_ui_searchpage_default'),
            }
        }
    };
</script>

<style>

</style>
