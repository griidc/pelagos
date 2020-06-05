<template>
    <b-card-group columns>
        <b-card-text>
            <b-card class="card-product" no-body v-for="person in personResearchGroups" v-bind:key="person.id">
                <b-card-body>
                    <p>
                        <strong>{{ person.person.firstName + " " + person.person.lastName }}</strong> <br> <br>
                        {{ person.label }} <br>
                        {{ person.person.organization }} <br>
                        {{ person.person.emailAddress }} <br>
                    </p>
                </b-card-body>
            </b-card>
        </b-card-text>
    </b-card-group>
</template>

<script>
    export default {
        name: "PeopleTab",
        props: {
            personResearchGroups: {},
        }
    }
</script>

<style scoped lang="scss">
    .card-columns {
        column-gap: 1rem !important;
        column-count: 2 !important;
        .card {
            height: 200px;
        }
    }
</style>
