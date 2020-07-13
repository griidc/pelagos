<template>
    <b-card-group columns>
        <b-card class="card-product"
                v-for="person in sortedPeople"
                :key="person.id"
                :title="person.person.firstName + ' ' + person.person.lastName"
                :sub-title="person.label">
            <b-card-text v-tooltip="{
                            content: person.person.organization,
                            placement:'top'
                            }">
                {{ person.person.organization | truncate(75) }}
            </b-card-text>
            <b-card-text>
                {{ person.person.emailAddress }}
            </b-card-text>
        </b-card>
    </b-card-group>
</template>

<script>
    export default {
        name: "PeopleTab",
        props: {
            personResearchGroups: {},
        },
        data() {
            return {
                sortedPeople: []
            }
        },
        created() {
            this.sortedPeople = this.$options.filters.sort('person.lastName', this.personResearchGroups);
        }
    }
</script>

<style scoped lang="scss">
    .card-columns {
        column-gap: 1rem !important;
        column-count: 2 !important;
        .card {
            height: 12em;
        }
    }
    .card-product {
        margin-bottom: 1rem;
    }

    .card-product:hover .btn-overlay {
        opacity: 1;
    }

    .card-product:hover {
        box-shadow: 0 4px 15px rgba(153, 153, 153, 0.3);
        transition: .5s;
    }

    @media (max-width: 992px) {
        .card-columns {
            column-count: 1 !important;
        }
    }
</style>
