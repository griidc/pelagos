<template>
    <div v-if="results.count > 0">
        <section class="section-content bg pt-3">
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
                <b-pagination
                        v-model="currentPage"
                        :total-rows="rows"
                        :per-page="perPage"
                        class="bg justify-content-center">
                </b-pagination>
                <div class="row">
                    <aside class="col-lg-3">
                        <div class="card card-filter">
                            <Facet :facet-info="results.facetInfo.statusInfo" :facet-name="datasetStatus" v-on="$listeners" :formValues="formValues"/>
                            <Facet :facet-info="results.facetInfo.fundingOrgInfo" :facet-name="fundingOrg" v-on="$listeners" :formValues="formValues"/>
                            <Facet :facet-info="results.facetInfo.researchGroupsInfo" :facet-name="researchGroup" v-on="$listeners" :formValues="formValues"/>
                        </div>
                    </aside>
                    <main class="col-lg-9 overflow-auto">
                        <DatasetRow :datasetRowData="resultRow" v-for="resultRow in results.resultData" v-bind:key="resultRow.udi"/>
                    </main>
                </div>
            </div>
        </section>
        <b-pagination
                v-model="currentPage"
                :total-rows="rows"
                :per-page="perPage"
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
    import DatasetRow from "./DatasetRow";
    export default {
        name: "ResultSet",
        components: { DatasetRow, Facet },
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