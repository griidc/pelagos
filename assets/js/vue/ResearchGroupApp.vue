<template>
    <div class="container-xl">
        <b-card no-body>
            <b-tabs pills card vertical>
                <b-tab title="Tab 1" active><b-card-text>Tab contents 1</b-card-text></b-tab>
                <b-tab title="Tab 2"><b-card-text>Tab contents 2</b-card-text></b-tab>
                <b-tab title="Tab 3"><b-card-text>Tab contents 3</b-card-text></b-tab>
            </b-tabs>
        </b-card>
    </div>
</template>

<script>
    const axios = require('axios');
    export default {
        name: "ResearchGroupApp",
        props: {
            id: {
                type: Number
            }
        },
        mounted() {
            console.log('mounted');
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
                    console.log(response);
                }).catch(error => {
                    console.log(error);
            });
        }
    }
</script>

<style scoped>

</style>