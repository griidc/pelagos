<template>
    <b-tab title="Datasets">
        <b-card-text>
            <b-card class="card-product" v-for="datasetInfo in datasetsRetrievedInfo">
                <b-link class="dataset-row" target="_blank" :href="datasetInfo.dataland">
                    <b-card-body>
                        <div class="row">
                            <article class="col-lg-12">
                                <b-card-text>
                                    {{ datasetInfo.citation }}
                                </b-card-text>
                            </article>
                        </div>
                    </b-card-body>
                </b-link>
            </b-card>
        </b-card-text>
    </b-tab>
</template>

<script>
    const axios = require('axios');
    export default {
        name: "DatasetsTab",
        props: {
            datasets: {}
        },
        data() {
            return {
                datasetsRetrievedInfo: []
            }
        },
        mounted() {
            this.datasets.forEach(dataset => {
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
                    .get(Routing.generate('pelagos_api_datasets_get_citation', { id: dataset.id }))
                    .then(response => {
                        let datasetInfo = {};
                        datasetInfo.citation = response.data;
                        datasetInfo.dataland = Routing.generate("pelagos_app_ui_dataland_default", {'udi' : dataset.udi });
                        this.datasetsRetrievedInfo.push(datasetInfo);
                    }).catch(error => {
                        console.log(error);
                });
            });
        }
    }
</script>

<style scoped>
    .card-product {
        margin-top: 10px;
    }
    a.dataset-row {
        font-size: 1.05em;
        text-decoration: none !important;
        color: black !important;
    }

    .dataset-row a:hover {
        text-decoration: none !important;
    }
    .card-product {
        margin-bottom: 1rem;
    }

    .card-product:hover .btn-overlay {
        opacity: 1;
    }

    .card-product:hover {
        -webkit-box-shadow: 0 4px 15px rgba(153, 153, 153, 0.3);
        box-shadow: 0 4px 15px rgba(153, 153, 153, 0.3);
        -webkit-transition: .5s;
        transition: .5s;
        cursor: pointer;
    }
</style>
