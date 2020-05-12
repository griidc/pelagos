<template>
    <b-card-text>
        <b-card class="card-product" no-body v-for="person in personResearchGroups" v-bind:key="person.id">
            <b-card-body>
                <p><strong>Name: </strong>{{ person.person.firstName + " " + person.person.lastName }}</p>
                <p><strong>Label: </strong>{{ person.label }}</p>
            </b-card-body>
        </b-card>
    </b-card-text>
</template>

<script>
    export default {
        name: "PeopleTab",
        props: {
            personResearchGroups: {},
        }
    }
</script>

<style scoped>
    .card-product {
        margin-top: 10px;
    }
</style>
