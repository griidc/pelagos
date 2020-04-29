<template>
    <div class="container-xl">
        <hr>
        <b-card no-body>
            <b-tabs pills card v-if="showData" lazy>
                <InfoTab :info="researchGroupData"/>
                <DatasetsTab :datasets="researchGroupData.datasets" />
                <PeopleTab :personResearchGroups="researchGroupData.personResearchGroups" />
                <b-tab title="Publications">
                    <b-card-text>
                        Alesia Ferguson, Helena Solo-Gabriele, Kristina Mena. 2020.
                        Child specific and beach characteristic dataset. Distributed by: GRIIDC,
                        Harte Research Institute, Texas A&M University-Corpus Christi. doi:10.7266/n7-rq3z-hq57
                    </b-card-text>
                </b-tab>
            </b-tabs>
        </b-card>
    </div>
</template>

<script>
    const axios = require('axios');
    import InfoTab from "./components/research-group/InfoTab";
    import DatasetsTab from "./components/research-group/DatasetsTab";
    import PeopleTab from "./components/research-group/PeopleTab";
    export default {
        name: "ResearchGroupApp",
        components: { InfoTab, DatasetsTab, PeopleTab},
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
