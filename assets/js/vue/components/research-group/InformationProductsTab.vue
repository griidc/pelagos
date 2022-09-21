<template>
  <div class="col-12">
    <InformationProductCard v-for="informationProduct in informationProductData"
                            :key="informationProduct.id" v-show="informationProduct.published"
                            :informationProduct="informationProduct"/>
  </div>
</template>

<script>
import InformationProductCard from '@/vue/components/information-product/InformationProductCard';

export default {
  name: 'InformationProductsTab',
  components: { InformationProductCard },
  props: {
    informationProductData: {},
  },
  data() {
    return {
      showData: false,
    };
  },
};
</script>

<style scoped lang="scss">

</style>
