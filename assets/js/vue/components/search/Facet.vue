<template>
    <article class="card-group-item">
        <header class="card-header">
            <h6 class="title">
                <strong v-if="facetName === 'status'">Dataset Status</strong>
                <strong v-else-if="facetName === 'fundingOrg'">Funding Organizations</strong>
                <strong v-else-if="facetName === 'researchGroup'">Research Groups</strong>
            </h6>
        </header>
        <div class="filter-content">
            <div class="card-body">
                <div class="input-group pb-3" v-show="facetName === 'researchGroup'">
                    <input class="form-control" placeholder="Search" type="text" name="research-grp-filter">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button">
                            <i class="fa fa-search"></i></button>
                    </div>
                </div>
                <form>
                    <div style="overflow-y: scroll; height: 10rem">
                        <label class="form-check" v-for="facet in facetInfo">
                            <input class="form-check-input facet-aggregation" value="" type="checkbox" :id="createIdForFacets(facet.id, facetName)">
                            <span class="form-check-label" v-if="facetName === 'status'">
                                <span class="float-right badge badge-light round">{{ facet.count }}</span>
                                {{ facet.name }}
                            </span>
                            <span class="form-check-label" v-b-tooltip.hover :title="facet.name" v-else>
                                <span class="float-right badge badge-light round">{{ facet.count }}</span>
                                 {{ facet.shortName }}
                            </span>
                        </label>
                    </div>
                </form>
            </div>
        </div>
    </article>
</template>

<script>
    export default {
        name: "FacetGroups",
        props: {
            facetInfo : {
                type: Array
            },
            facetName: {
                type: String
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