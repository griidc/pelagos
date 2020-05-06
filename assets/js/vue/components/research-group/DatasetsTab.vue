<template>
    <b-card-text>
        <b-card class="card-product" v-for="dataset in datasets">
            <b-link class="dataset-row" target="_blank" :href="getDatalandRoute(dataset.udi)">
                <b-card-body>
                    <div class="row">
                        <article class="col-lg-12">
                            <b-card-text>
                                {{ dataset.citation }}
                            </b-card-text>
                        </article>
                    </div>
                </b-card-body>
            </b-link>
        </b-card>
    </b-card-text>
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
        methods: {
            getDatalandRoute: function (udi) {
                return Routing.generate("pelagos_app_ui_dataland_default", {'udi' : udi });
            }
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
