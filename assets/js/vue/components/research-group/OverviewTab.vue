<template>
    <div>
        <h4>{{ overview.name }}</h4>
        <hr>
        <h6 class="font-weight-bold">Project Director: </h6>
        <p v-for="director in getDirectors()" v-bind:key="director.person.id">
            {{ director.person.firstName + " " + director.person.lastName }}
            ({{ director.person.organization }})
        </p>
        <h6 class="font-weight-bold">Funding Organization</h6>
        <p>
            <a :href=overview.fundingCycle.fundingOrganization.url>
                {{ overview.fundingCycle.fundingOrganization.name }}
            </a>
        </p>
        <h6 class="font-weight-bold">{{ getFCLabel() }}</h6>
        <p>
            <a :href="overview.fundingCycle.url">
                {{ overview.fundingCycle.name }}
            </a>
        </p>
        <h6 class="font-weight-bold">Description</h6>
        <p>{{ overview.description }}</p>
    </div>
</template>

<script>
import templateSwitch from '@/vue/utils/template-switch';

export default {
  name: 'OverviewTab',
  props: {
    overview: {
      type: Object,
    },
  },
  methods: {
    getDirectors() {
      return this.overview.personResearchGroups.filter((person) => {
        if (person.role.name === 'Leadership') {
          return person;
        }
        return null;
      });
    },
    getFCLabel() { return templateSwitch.getProperty('fundingCycle'); },
  },
};
</script>

<style scoped>

</style>
