<template>
    <article class="card card-product">
        <div class="card-body">
            <div class="row">
                <article class="col-sm-12">
                    <span class="badge badge-secondary" v-if="resultRowData.availabilityStatus === 0">Identified</span>
                    <span class="badge badge-primary" v-else-if="resultRowData.availabilityStatus === 2 || resultRowData.availabilityStatus === 4">Submitted</span>
                    <span class="badge badge-danger" v-else-if="resultRowData.availabilityStatus === 5 || resultRowData.availabilityStatus === 8">Restricted</span>
                    <span class="badge badge-success" v-else-if="resultRowData.availabilityStatus === 7 || resultRowData.availabilityStatus === 10">Available</span>
                    <p>
                        <a class="search-result"
                           :href="url" target="_blank">
                            {{ resultRowData.datasetSubmission.authors }}.
                            {{ resultRowData.year }}.
                            {{ resultRowData.title }}.
                            {{ resultRowData.researchGroup.name }}.
                            DOI: {{ resultRowData.doi.doi }}.
                            UDI: {{ resultRowData.udi }}
                        </a>
                    </p>
                </article>
            </div>
        </div>
    </article>
</template>

<script>
    export default {
        name: "ResultRow",
        props: {
            resultRowData: {
                type: Object
            }
        },
        data: function () {
            return {
                url: Routing.generate("pelagos_app_ui_dataland_default", {'udi' : this.resultRowData.udi } )
            }
        }
    }
</script>

<style scoped>

</style>