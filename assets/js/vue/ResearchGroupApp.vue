<template>
    <div class="container-xl" ref="formContainer">
        <hr>
        <b-card no-body class="main-card">
            <b-tabs pills fill justified card v-if="showData" lazy vertical class="min-vh-100">
                <b-tab title="Overview">
                    <OverviewTab :overview="researchGroupData"/>
                </b-tab>
                <b-tab title="Datasets">
                    <DatasetsTab :datasets="researchGroupData.datasets" />
                </b-tab>
                <b-tab title="People">
                    <PeopleTab :personResearchGroups="researchGroupData.personResearchGroups" />
                </b-tab>
                <b-tab title="Publications">
                    <PublicationsTab :datasets="researchGroupData.datasets" />
                </b-tab>
            </b-tabs>
        </b-card>
    </div>
</template>

<script>
    import PublicationsTab from "./components/research-group/PublicationsTab";
    const axios = require('axios');
    import OverviewTab from "./components/research-group/OverviewTab";
    import DatasetsTab from "./components/research-group/DatasetsTab";
    import PeopleTab from "./components/research-group/PeopleTab";
    export default {
        name: "ResearchGroupApp",
        components: { PublicationsTab, OverviewTab, DatasetsTab, PeopleTab },
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
        mounted() {
            let loader = null;
            let thisComponent = this;
            const axiosInstance = axios.create({});
            axiosInstance.interceptors.request.use(function (config) {
                loader = thisComponent.$loading.show({
                    container: thisComponent.$refs.formContainer,
                    loader: 'bars',
                    color: '#007bff',
                });
                return config;
            }, function (error) {
                return Promise.reject(error);
            });

            function hideLoader(){
                loader && loader.hide();
                loader = null;
            }

            axiosInstance.interceptors.response.use(function (response) {
                hideLoader();
                return response;
            }, function (error) {
                hideLoader();
                return Promise.reject(error);
            });

            axiosInstance
                .get(Routing.generate('pelagos_api_research_groups_get') + "/" + this.id)
                .then(response => {
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
