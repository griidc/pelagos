<template>
    <b-card-group>
        <PublicationRow :publicationRow="publication" v-for="publication in publications" v-bind:key="publication.id"/>
    </b-card-group>
</template>

<script>
import PublicationRow from '@/vue/components/research-group/PublicationRow';

export default {
  name: 'PublicationsTab',
  components: { PublicationRow },
  props: {
    publications: {},
  },
};
</script>

<style scoped>

</style>
