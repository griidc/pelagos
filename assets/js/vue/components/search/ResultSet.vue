<template>
    <div>
        <section class="section-content bg pt-5">
            <div class="container">
                <div class="row d-flex flex-row justify-content-center">
                    <h3 id="count">
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
                            <Facet :facet-info="results.facetInfo.statusInfo" :facet-name="datasetStatus"/>
                            <Facet :facet-info="results.facetInfo.fundingOrgInfo" :facet-name="fundingOrg"/>
                            <Facet :facet-info="results.facetInfo.researchGroupsInfo" :facet-name="researchGroup"/>
                        </div>
                    </aside>
                    <main class="col-sm-9 overflow-auto">
                        <ResultRow :resultRowData="resultRow" v-for="resultRow in results.resultData" v-bind:key="resultRow.udi"/>
                    </main>
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
            }
        },
        data: function () {
            return {
                datasetStatus: 'status',
                fundingOrg: 'fundingOrg',
                researchGroup: 'researchGroup'
            }
        }
    }
</script>

<style scoped>

</style>