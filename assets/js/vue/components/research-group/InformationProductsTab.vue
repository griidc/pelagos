<template>
  <b-card-group deck>
    <b-card class="card-product my-2"
            v-for="informationProduct in informationProductData"
            :key="informationProduct.id"
            :title="informationProduct.title">
      <b-card-text>
        Creators: {{ informationProduct.creators }}
      </b-card-text>
      <b-card-text>
        Publisher: {{ informationProduct.publisher }}
      </b-card-text>
      <b-card-text class="text-muted">
        {{ informationProduct.externalDoi }}
      </b-card-text>
    </b-card>
  </b-card-group>
</template>

<script>
import { getApi } from '@/vue/utils/axiosService';

export default {
  name: 'InformationProductsTab',
  props: {
    rgId: Number,
  },
  data() {
    return {
      informationProductData: [],
      showData: false,
    };
  },
  created() {
    getApi(
      // eslint-disable-next-line no-undef
      `${Routing.generate('pelagos_api_get_information_product_by_research_group_id')}/${this.rgId}`,
      { thisComponent: this, addLoading: true },
    ).then((response) => {
      this.informationProductData = response.data;
      this.showData = true;
    }).catch(() => {
      this.showData = false;
    });
  },
};
</script>

<style scoped>

</style>
