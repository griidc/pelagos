<template>
    <b-card-group columns>
        <b-card-text>
            <b-card class="card-product" no-body v-for="person in personResearchGroups" v-bind:key="person.id">
                <b-card-body>
                    <div>
                        <p>
                            <strong>{{ person.person.firstName + " " + person.person.lastName }}</strong> <br> <br>
                        </p>
                    </div>
                    <div>
                        {{ person.label }}
                    </div>
                    <div class="text-overflow"
                         v-tooltip="{
                                    content: person.person.organization,
                                    placement:'top'
                                    }"
                    >
                        {{ person.person.organization }}
                    </div>
                    <div>
                        {{ person.person.emailAddress }}
                    </div>

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

    .text-overflow {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 250px;
    }
</style>
