<template>
    <div class="container" ref="formContainer">
        <hr>
        <b-card no-body class="main-card">
            <b-tabs pills fill justified card v-if="showData" lazy vertical class="min-vh-100">
                <b-tab title="Overview">
                    <OverviewTab :overview="researchGroupData"/>
                </b-tab>
                <b-tab title="Datasets">
                  <template #title>
                    <span class="mr-3">Datasets</span>
                    <span class="float-right badge badge-pill badge-secondary">
                      {{ researchGroupData.datasets.length }}
                    </span>
                  </template>
                    <DatasetsTab :datasets="researchGroupData.datasets"/>
                </b-tab>
                <b-tab title="People">
                  <template #title>
                    <span class="mr-3">People</span>
                    <span class="float-right badge badge-pill badge-secondary">
                      {{ researchGroupData.personResearchGroups.length }}
                    </span>
                  </template>
                    <PeopleTab :personResearchGroups="researchGroupData.personResearchGroups"/>
                </b-tab>
                <b-tab title="Publications">
                  <template #title>
                    <span class="mr-3">Publications</span>
                    <span class="float-right badge badge-pill badge-secondary">
                      {{ getPublications().length }}
                    </span>
                  </template>
                    <PublicationsTab :publications="getPublications()"/>
                </b-tab>
                <b-tab title="Information Products">
                  <template #title>
                    <span class="mr-3">Information Products</span>
                    <span class="float-right badge badge-pill badge-secondary">
                      {{ informationProductData.length }}
                    </span>
                  </template>
                    <InformationProductsTab :informationProductData="informationProductData"/>
                </b-tab>
            </b-tabs>
        </b-card>
    </div>
</template>

<script>
import PublicationsTab from '@/vue/components/research-group/PublicationsTab';
import OverviewTab from '@/vue/components/research-group/OverviewTab';
import DatasetsTab from '@/vue/components/research-group/DatasetsTab';
import PeopleTab from '@/vue/components/research-group/PeopleTab';
import InformationProductsTab from '@/vue/components/research-group/InformationProductsTab';
import { getApi } from '@/vue/utils/axiosService';

export default {
  name: 'ResearchGroupApp',
  components: {
    PublicationsTab, OverviewTab, DatasetsTab, PeopleTab, InformationProductsTab,
  },
  props: {
    id: {
      type: Number,
    },
  },
  data() {
    return {
      researchGroupData: {},
      showData: false,
      informationProductData: {},
    };
  },
  methods: {
    getPublications() {
      const publications = [];
      const publicationId = [];
      this.researchGroupData.datasets.forEach((dataset) => {
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
  created() {
    getApi(
      // eslint-disable-next-line no-undef
      `${Routing.generate('pelagos_api_research_group')}/${this.id}`,
      { thisComponent: this, addLoading: true },
    ).then((response) => {
      this.researchGroupData = response.data;
      this.showData = true;
      getApi(
        // eslint-disable-next-line no-undef
        `${Routing.generate('pelagos_api_get_information_product_by_research_group_id')}/${this.researchGroupData.id}`,
        { thisComponent: this, addLoading: true },
      ).then((infoProdResponse) => {
        this.informationProductData = infoProdResponse.data;
      });
    }).catch(() => {
      this.showData = false;
    });
  },
};
</script>

<style scoped lang="scss">
.badge-pill {
  top: 3px;
  position: relative;
}
</style>
