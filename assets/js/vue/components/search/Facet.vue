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
                    <input class="form-control" placeholder="Search" type="text" v-model="researchGroupSearch">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button">
                            <i class="fa fa-search"></i></button>
                    </div>
                </div>
                <form>
                    <div v-bind:class="facetScrollable">
                        <label class="form-check" v-for="facet in filteredFacets">
                            <input class="form-check-input facet-aggregation"
                                   :value="facet.id" type="checkbox"
                                   :id="facetName + '_' + facet.id"
                                   v-model="listOfCheckedFacets"
                                   @change="facetChange">
                            <span class="form-check-label"
                                  v-if="facetName === 'status'"
                                  v-tooltip="{
                                    content: statusTooltip(facet.name),
                                    placement:'top'
                                    }">
                                <span class="float-right badge badge-light round">{{ facet.count }}</span>
                                {{ facet.name }}
                            </span>
                            <span class="form-check-label"
                                  v-tooltip="{
                                    content: facet.name,
                                    placement:'top'
                                    }"
                                  v-else>
                                <span class="float-right badge badge-light round">{{ facet.count }}</span>
                                 {{ facet.shortName ? facet.shortName : facet.name }}
                            </span>

                        </label>
                    </div>
                </form>
            </div>
        </div>
    </article>
</template>

<script>
    const maxFacetsToDisplay = 10;
    export default {
        name: "FacetGroups",
        props: {
            facetInfo : {
                type: Array
            },
            facetName: {
                type: String
            },
            formValues: {
                type: Object
            }
        },
        data: function() {
          return {
              researchGroupSearch: '',
              listOfCheckedFacets: []
          }
        },
        methods: {
            facetChange: function () {
                this.$emit('facetClicked', this.facetName + '=' + this.listOfCheckedFacets.join(","));
            },
            facetCheckBox: function () {
                if (this.facetName in this.formValues) {
                    if (this.formValues[this.facetName]) {
                        let splitFacets = this.formValues[this.facetName].split(",");
                        this.listOfCheckedFacets = [];
                        splitFacets.forEach((value) => {
                            this.listOfCheckedFacets.push(value);
                        });
                    } else {
                        this.listOfCheckedFacets = [];

                    }
                }
            },
            statusTooltip: function (datasetStatus) {
                let datasetStatusTooltip = "";
                switch (true) {
                    case (datasetStatus === "Available"):
                        datasetStatusTooltip = "This dataset is available for download.";
                        break;
                    case (datasetStatus === "Restricted"):
                        datasetStatusTooltip = "This dataset is restricted for download.";
                        break;
                    case (datasetStatus === "Submitted"):
                        datasetStatusTooltip = "This dataset has been submitted and is not available for download.";
                        break;
                    case (datasetStatus === "Identified"):
                        datasetStatusTooltip = "This dataset has not been submitted and is not available for download.";
                        break;
                }
                return datasetStatusTooltip;
            }
        },
        computed: {
            filteredFacets: function () {
                if (this.facetName === 'researchGroup') {
                    return this.facetInfo.filter(facetItem => {
                        const facetItemName = facetItem.shortName + facetItem.name;
                        return facetItemName.toLowerCase().indexOf(this.researchGroupSearch.toLowerCase()) > -1;
                    })
                } else {
                    return this.facetInfo;
                }
            },
            facetScrollable: function () {
                const scrollableClass = 'scrollable-facet';
                if (this.facetInfo.length > maxFacetsToDisplay) {
                    return scrollableClass;
                }
            }
        },
        created() {
            this.facetCheckBox();
        },
        watch: {
            formValues: function () {
                this.facetCheckBox();
            },
        }
    }
</script>

<style scoped>
    .scrollable-facet {
        height: auto;
        max-height: 20rem;
        overflow-y: auto;
    }
</style>
