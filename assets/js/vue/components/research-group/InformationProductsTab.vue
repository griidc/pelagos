<template>
  <div class="col-12">
    <b-card class="card-product"
            v-for="informationProduct in informationProductData"
            :key="informationProduct.id">
      <div>
        <span class="badge badge-available">Product Type</span>
        <span class="badge badge-submitted">Digital Resource Type</span>
      </div>
      <b-card-title style="font-size: 1.3rem !important;">{{ informationProduct.title }}</b-card-title>
      <b-card-text class="d-flex justify-content-between" >
        <div v-if="Object.keys(informationProduct).length > 0">
          <div v-if="informationProduct.creators" style="max-width: 550px">
            Creators: {{ informationProduct.creators }}
          </div>
          <div v-if="informationProduct.publisher">
            Publisher: {{ informationProduct.publisher }}
          </div>
        </div>
        <div>
          <div v-if="informationProduct.externalDoi">
            DOI:{{ informationProduct.externalDoi }}
          </div>
          <div v-if="informationProduct.file && informationProduct.file.status === 'done'">
            File:
            <a :href="`${downloadUrl}/${informationProduct.id}`">
              {{ informationProduct.file.filePathName }}
            </a> ({{ humanSize(informationProduct.file.fileSize) }})
          </div>
          <div v-if="informationProduct.remoteUri">
            Remote Link:
            <a :href="informationProduct.remoteUri" target="_BLANK">
              {{ informationProduct.remoteUri }}
            </a>
          </div>
        </div>
      </b-card-text>
    </b-card>
  </div>
</template>

<script>
import { getApi } from '@/vue/utils/axiosService';
import xbytes from 'xbytes';

export default {
  name: 'InformationProductsTab',
  props: {
    rgId: Number,
  },
  data() {
    return {
      informationProductData: [],
      showData: false,
      // eslint-disable-next-line no-undef
      downloadUrl: `${Routing.generate('pelagos_api_ip_file_download')}`,
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
  methods: {
    humanSize(fileSize) {
      return xbytes(fileSize);
    },
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
