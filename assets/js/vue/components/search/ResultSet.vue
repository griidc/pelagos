<template>
    <div v-if="results.count > 0">
        <section class="section-content bg pt-5">
            <div class="container">
                <div class="row d-flex flex-row justify-content-center">
                    <h3>
                        Found {{ results.count }} results for: "{{ results.formValues.query }}"
                    </h3>
                </div>
            </div>
        </section>
        <section class="section-content bg padding-y">
            <div class="container">
                <div class="row">
                    <aside class="col-sm-3">
                        <div class="card card-filter">
                            <Facet :facet-info="results.facetInfo.statusInfo" :facet-name="datasetStatus" v-on="$listeners" :formValues="formValues"/>
                            <Facet :facet-info="results.facetInfo.fundingOrgInfo" :facet-name="fundingOrg" v-on="$listeners" :formValues="formValues"/>
                            <Facet :facet-info="results.facetInfo.researchGroupsInfo" :facet-name="researchGroup" v-on="$listeners" :formValues="formValues"/>
                        </div>
                    </aside>
                    <main class="col-sm-9 overflow-auto">
                        <ResultRow :resultRowData="resultRow" v-for="resultRow in results.resultData" v-bind:key="resultRow.udi"/>
                    </main>
                </div>
            </div>
        </section>
        <b-pagination
                v-model="currentPage"
                :total-rows="rows"
                :per-page="perPage"
                aria-controls="my-table"
                class="bg justify-content-center"
                style="margin-bottom: 100px;">
        </b-pagination>
    </div>
    <div v-else>
        <section class="section-content bg pt-5" >
            <div class="container">
                <div class="row d-flex flex-row justify-content-center">
                    <h3>
                        No results found!
                    </h3>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import Facet from "./Facet";
    import ResultRow from "./ResultRow";
    export default {
        name: "ResultSet",
        components: {ResultRow, Facet },
        props: {
            results: {
                type: Object
            },
            formValues: {
                type: Object
            }
        },
        data: function () {
            return {
                datasetStatus: 'status',
                fundingOrg: 'fundingOrg',
                researchGroup: 'researchGroup',
                facetCheckBoxes: {
                    status: '',
                    fundingOrg: '',
                    researchGroup: ''
                },
                currentPage: 1,
                perPage: 10,
            }
        },
        computed: {
            rows: function () {
                return this.results.count;
            }
        },
        watch: {
            currentPage: function (value) {
                this.$emit('pagination', value);
            }
        }
    }
</script>

<style scoped>

</style>