<template>
    <b-card-group>
        <PublicationRow :publicationRow="publication" v-for="publication in getPublications()" v-bind:key="publication.id"/>
    </b-card-group>
</template>

<script>
import PublicationRow from '@/vue/components/research-group/PublicationRow';

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
        dataset.datasetPublications.forEach((publication) => {
          if (!publicationId.includes(publication.publication.id)) {
            publications.push(publication.publication);
            publicationId.push(publication.publication.id);
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
