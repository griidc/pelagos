<template>
    <b-card-group>
        <PublicationRow :publicationRow="publication" v-for="publication in getPublications()" v-bind:key="publication.id"/>
    </b-card-group>
</template>

<script>
import PublicationRow from './PublicationRow';

export default {
  name: 'PublicationsTab',
  components: { PublicationRow },
  props: {
    datasets: {},
  },
  methods: {
    getPublications() {
      const publications = [];
      const publicationId = [];
      this.datasets.forEach((dataset) => {
        dataset.publications.forEach((publication) => {
          if (!publicationId.includes(publication.id)) {
            publications.push(publication);
            publicationId.push(publication.id);
          }
        });
      });
      return publications;
    },
  },
};
</script>

<style scoped>

</style>
