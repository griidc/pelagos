<template>
  <div class="col-12">
    <InformationProductCard v-for="informationProduct in informationProductData"
                            :key="informationProduct.id" v-show="informationProduct.published"
                            :informationProduct="informationProduct"/>
  </div>
</template>

<script>
import { getApi } from '@/vue/utils/axiosService';
import InformationProductCard from '@/vue/components/information-product/InformationProductCard';

export default {
  name: 'InformationProductsTab',
  components: { InformationProductCard },
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

<style scoped lang="scss">
.card-product {
  margin-bottom: 1rem;
  transition: .5s;

  &:hover {
    .btn-overlay {
      opacity: 1;
    }

    box-shadow: 0 4px 15px rgba(153, 153, 153, 0.3);
    transition: .5s;
    cursor: pointer;
  }
}
.card-body {
  padding: 0.625rem !important;
}

</style>
