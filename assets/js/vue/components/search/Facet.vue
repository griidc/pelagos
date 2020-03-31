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
                    <div v-bind:style="facetName === 'researchGroup' ? 'overflow-y: scroll; height: 20rem;': ''">
                        <label class="form-check" v-for="facet in filteredFacets">
                            <input class="form-check-input facet-aggregation" :value="facet.id" type="checkbox" :id="facetName + '_' + facet.id" v-model="listOfCheckedFacets" @change="facetChange">
                            <span class="form-check-label" v-if="facetName === 'status'">
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
            }
        },
        computed: {
            filteredFacets: function () {
                if (this.facetName === 'researchGroup') {
                    return this.facetInfo.filter(facetItem => {
                        if (facetItem.shortName) {
                            return facetItem.shortName.toLowerCase().indexOf(this.researchGroupSearch.toLowerCase()) > -1;
                        } else {
                            return facetItem.name.toLowerCase().indexOf(this.researchGroupSearch.toLowerCase()) > -1;
                        }
                    })
                } else {
                    return this.facetInfo;
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

</style>