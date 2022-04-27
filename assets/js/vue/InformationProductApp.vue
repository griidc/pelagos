<template>
    <div class="bootstrap">
        <div class="container">
            <InformationProductForm/>
        </div>
    </div>
</template>

<script>
import InformationProductForm from '@/vue/components/information-product/InformationProductForm';

export default {
  name: 'InformationProductApp',
  components: { InformationProductForm },
};
</script>

<style scoped>

</style>
