<template>
    <b-card-group>
        <PublicationRow :publicationRow="dataset" v-for="dataset in datasets" v-bind:key="dataset.id"/>
    </b-card-group>
</template>

<script>
    import PublicationRow from "./PublicationRow";
    export default {
        name: "PublicationsTab",
        components: { PublicationRow },
        props: {
            datasets: {}
        },
    }
</script>

<style scoped>

</style>
