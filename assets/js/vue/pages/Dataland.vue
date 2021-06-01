<template>
    <div class="container">
        <div class="row" v-show="dataset.length > 0">
            <main class="col-lg-9 overflow-auto">
                <div class="row">
                    <h4>Abstract Etc</h4>
                </div>
            </main>
            <aside class="col-lg-3">
                <b-card header="Downloads" header-bg-variant="primary" class="text-center text-light">
                    <b-card-text class="text-dark">
                        {{ downloadCount }}
                    </b-card-text>
                </b-card>
                <b-card header="Details" header-bg-variant="primary" class="text-center text-light">
                    <b-card-text class="text-dark">
                        <p>
                            Research Group:
                            <b-badge v-show="dataset.length > 0">{{ dataset.researchGroup }}</b-badge>
                        </p>
<!--                        <p>-->
<!--                            Funded By: <br>-->
<!--                            <b-badge v-show="dataset.length > 0">{{ dataset.researchGroup.fundingCycle.fundingOrganization.shortName }}</b-badge>-->
<!--                        </p>-->
                    </b-card-text>
                </b-card>
            </aside>
        </div>
    </div>
</template>

<script>
import {getApi} from "@/vue/utils/axiosService";

export default {
    name: "Dataland",
    props: {
        udi: {
            type: String
        }
    },
    data() {
        return {
            dataset: {},
            downloadCount: 0
        }
    },
    created() {
        getApi(
            `${Routing.generate('pelagos_api_datasets_get')}?udi=${this.udi}&_properties=datasetSubmission,
            doi,datasetSubmission.fileset, researchGroup, researchGroup.fundingCycle.fundingOrganization`,
            {thisComponent: this, addLoading: true}
        ).then(response => {
            console.log(response.data);
            this.dataset = response.data[0];
            getApi(`${Routing.generate('pelagos_app_ui_dataland_download_count')}/${this.dataset.id}`, {thisComponent: this, addLoading: true})
            .then(response => {
                this.downloadCount = response.data.downloadCount;
            })
        })
    }
}
</script>

<style scoped>
.card {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, .1), 0 1px 2px 0 rgba(0, 0, 0, .06);
    margin: 1rem;
}
</style>