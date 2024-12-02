<template>
    <b-card-group deck>
        <router-link
            v-for="person in sortedPeople"
            :key="person.id"
            :to="{ name: 'app_person_land', params: { person: person.person.id } }"
            class="block"
        >
            <b-card class="card-product my-2"
                    :title="person.person.firstName + ' ' + person.person.lastName"
                    :sub-title="person.label" style="min-width: 18rem; max-width: 27rem">
                <b-card-text v-tooltip="{
                                content: person.person.organization,
                                placement:'top'
                                }">
                    {{ person.person.organization | truncate(75) }}
                </b-card-text>
                <b-card-text class="text-muted">
                    {{ person.person.emailAddress }}
                </b-card-text>
            </b-card>
        </router-link>
    </b-card-group>
</template>

<script>
export default {
  name: 'PeopleTab',
  props: {
    personResearchGroups: {},
  },
  data() {
    return {
      sortedPeople: [],
    };
  },
  created() {
    this.sortedPeople = this.$options.filters.sort('person.lastName', this.personResearchGroups);
  },
};
</script>

<style scoped lang="scss">
    .card-product {
        margin-bottom: 1rem;
        transition: .5s;
    }

    .card-product:hover .btn-overlay {
        opacity: 1;
    }

    .card-product:hover {
        box-shadow: 0 4px 15px rgba(153, 153, 153, 0.3);
        transition: .5s;
    }
</style>
