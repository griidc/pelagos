<template>
    <aside class="col-sm-3">
        <div class="card card-filter">
            <!-- Dataset Status Facet -->
            <article class="card-group-item">
                <header class="card-header">
                    <h6 class="title"><strong>Dataset Status</strong></h6>
                </header>
                <div class="filter-content">
                    <div class="card-body">
                        <form id="status-facet">
                            <label class="form-check" v-for="status in facets.statusInfo">
                                <input class="form-check-input facet-aggregation" value="" type="checkbox" :id="createIdForFacets(status.id, 'status')">
                                <span class="form-check-label">
                                        <span class="float-right badge badge-light round">{{ status.count }}</span>
                                        {{ status.name }}
                                    </span>
                            </label>
                        </form>
                    </div>
                </div>
            </article>

            <!-- Funding Organizations Facet -->
            <article class="card-group-item">
                <header class="card-header">
                    <h6 class="title"><strong>Funding Organizations</strong></h6>
                </header>
                <div style="" class="filter-content">
                    <div class="card-body">
                        <form id="fundorg-facet">
                            <label class="form-check" v-for="fundingOrg in facets.fundingOrgInfo">
                                <input class="form-check-input facet-aggregation" value="" type="checkbox" :id="createIdForFacets(fundingOrg.id, 'fundingOrg')">
                                <span class="form-check-label" data-toggle="tooltip" :title="fundingOrg.name">
                                    <span class="float-right badge badge-light round">{{ fundingOrg.count }}</span>
                                    {{ fundingOrg.shortName }}
                                </span>
                            </label>
                        </form>
                    </div>
                </div>
            </article>

            <!-- Research Groups Facet -->
            <article class="card-group-item">
                <header class="card-header">
                    <h6 class="title"><strong>Research Groups</strong></h6>
                </header>
                <div class="filter-content">
                    <div class="card-body">
                        <div class="input-group pb-3">
                            <input class="form-control" placeholder="Search" type="text" id="research-grp-filter">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fa fa-search"></i></button>
                            </div>
                        </div>
                        <form id="resgrp-facet">
                            <div style="overflow-y: scroll; height: 10rem">

                                <label class="form-check" :id="researchGroup.id" v-for="researchGroup in facets.researchGroupsInfo">
                                    <input class="form-check-input facet-aggregation" :value="researchGroup.shortName" type="checkbox" :id="createIdForFacets(researchGroup.id, 'researchGroup')">
                                    <span class="form-check-label" data-toggle="tooltip" :title="researchGroup.name">
                                        <span class="float-right badge badge-light round">{{ researchGroup.count }}</span>
                                        {{researchGroup.shortName}}
                                    </span>
                                </label>
                            </div>
                        </form>
                    </div>
                </div>
            </article>
        </div>
    </aside>
</template>

<script>
    export default {
        name: "FacetGroups",
        props: {
            facets : {
                type: Object
            }
        },
        methods: {
            createIdForFacets: function (id, facetName) {
                return `${facetName}_${id}`;
            }
        }
    }
</script>

<style scoped>

</style>