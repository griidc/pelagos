<template>
    <div>
        <b-card class="card-product" @click="openUrl(publication.doi)" v-for="publication in publicationRow.publications" v-bind:key="publication.id">
            <b-card-text class="d-flex justify-content-between" >
                {{ publication.citationText }}
            </b-card-text>
        </b-card>
    </div>
</template>

<script>
    export default {
        name: "PublicationRow",
        props: {
            publicationRow: {
                type: Object
            }
        },
        methods: {
            openUrl: function(doi) {
                if ("" === window.getSelection().toString()) {
                    window.open("https://dx.doi.org/" + doi, '_blank');
                }
            }
        }
    }
</script>

<style scoped>
    .card-product {
        margin-bottom: 1rem;
    }

    .card-product:hover .btn-overlay {
        opacity: 1;
    }

    .card-product:hover {
        box-shadow: 0 4px 15px rgba(153, 153, 153, 0.3);
        transition: .5s;
        cursor: pointer;
    }
</style>
