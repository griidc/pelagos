<template>
    <div>
        <h4>{{ overview.name }}</h4>
        <hr>
        <h6 class="overview-label">Project Director(s): </h6>
        <p v-for="director in getDirectors()">
            {{ director.person.firstName + director.person.lastName }}
            ({{ director.person.organization }})
        </p>
        <h6 class="overview-label">Funding Organization</h6>
        <p>
            <a :href=overview.fundingCycle.fundingOrganization.url>
                {{ overview.fundingCycle.fundingOrganization.name }}
            </a>
        </p>
        <h6 class="overview-label">Funding Cycle</h6>
        <p>
            <a :href="overview.fundingCycle.url">
                {{ overview.fundingCycle.name }}
            </a>
        </p>
        <h6 class="overview-label">Description</h6>
        <p>{{ overview.description }}</p>
    </div>
</template>

<script>
    export default {
        name: "OverviewTab",
        props: {
            overview: {
                type: Object
            }
        },
        methods: {
            getDirectors: function () {
                return this.overview.personResearchGroups.filter(person => {
                    if (person.role.name === "Leadership") {
                        return person;
                    }
                });
            }
        }
    }
</script>

<style scoped>
    .overview-label {
        font-weight: bold;
    }
</style>
