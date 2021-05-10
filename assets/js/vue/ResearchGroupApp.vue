<template>
    <div class="container" ref="formContainer">
        <hr>
        <b-card no-body class="main-card">
            <b-tabs pills fill justified card v-if="showData" lazy vertical class="min-vh-100">
                <b-tab title="Overview">
                    <OverviewTab :overview="researchGroupData"/>
                </b-tab>
                <b-tab title="Datasets">
                    <DatasetsTab :datasets="researchGroupData.datasets"/>
                </b-tab>
                <b-tab title="People">
                    <PeopleTab :personResearchGroups="researchGroupData.personResearchGroups"/>
                </b-tab>
                <b-tab title="Publications">
                    <PublicationsTab :datasets="researchGroupData.datasets"/>
                </b-tab>
            </b-tabs>
        </b-card>
    </div>
</template>

<script>
import PublicationsTab from "./components/research-group/PublicationsTab";
import OverviewTab from "./components/research-group/OverviewTab";
import DatasetsTab from "./components/research-group/DatasetsTab";
import PeopleTab from "./components/research-group/PeopleTab";
import {getApi} from "@/vue/utils/axiosService";

export default {
    name: "ResearchGroupApp",
    components: {PublicationsTab, OverviewTab, DatasetsTab, PeopleTab},
    props: {
        id: {
            type: Number
        }
    },
    data() {
        return {
            researchGroupData: {},
            showData: false
        }
    },
    created() {
        getApi(
            Routing.generate('pelagos_api_research_groups_get') + "/" + this.id,
            {thisComponent: this, addLoading: true}
        ).then(response => {
            this.researchGroupData = response.data;
            this.showData = true;
        }).catch(error => {
            console.log(error);
            this.showData = false;
        });
    }
}
</script>

<style scoped>

</style>
