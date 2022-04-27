<template>
    <div class="bootstrap">
        <div class="container">
            <InformationProductList/>
        </div>
    </div>
</template>

<script>
import InformationProductList from '@/vue/components/information-product/InformationProductList';

export default {
  name: 'InformationProductListApp',
  components: { InformationProductList },
};
</script>

<style scoped>

</style>
