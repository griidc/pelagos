<template>
  <b-card class="card-product" @click="infoProdUrl()">
    <div>
        <span class="badge badge-itemtype">Information Product</span>
        <span class="badge badge-available mr-1"
              v-for="productType in informationProduct.productTypeDescriptors"
              :key="productType.id">
          {{ productType.description }}
        </span>
      <span class="badge badge-submitted mr-1"
            v-for="digitalResourceType in informationProduct.digitalResourceTypeDescriptors"
            :key="digitalResourceType.id">
          {{ digitalResourceType.description }}
        </span>
    </div>
    <b-card-title style="font-size: 1.3rem !important;">{{ informationProduct.title }}</b-card-title>
    <b-card-text class="d-flex justify-content-between" >
      <div v-if="Object.keys(informationProduct).length > 0">
        <div v-if="informationProduct.creators" style="max-width: 550px">
          Creators: {{ truncateText(informationProduct.creators, 100) }}
        </div>
        <div v-if="informationProduct.publisher">
          Publisher: {{ truncateText(informationProduct.publisher, 100) }}
        </div>
      </div>
      <div>
        <div v-if="informationProduct.externalDoi">
          DOI:{{ informationProduct.externalDoi }}
        </div>
        <div v-if="informationProduct.file && informationProduct.file.status === 'done'">
          {{ informationProduct.file.fileExtension }}
          ({{ humanSize(informationProduct.file.fileSize) }})
        </div>
        <div v-if="informationProduct.remoteUri" class="float-right">
          {{ informationProduct.remoteUriHostName }}
        </div>
      </div>
    </b-card-text>
  </b-card>
</template>

<script>
import xbytes from 'xbytes';
import { truncate } from '../../utils/filters';

export default {
  name: 'InformationProductCard',
  props: {
    informationProduct: Object,
  },
  methods: {
    humanSize(fileSize) {
      return xbytes(fileSize);
    },
    infoProdUrl() {
      if (window.getSelection().toString() === '') {
        window.open(
          // eslint-disable-next-line no-undef
          `${Routing.generate('pelagos_app_ui_info_product_landing',
            { id: this.informationProduct.id })}`, '_blank',
        );
      }
    },
    openRemoteUrl() {
      if (window.getSelection().toString() === '') {
        window.open(
          // eslint-disable-next-line no-undef
          `${this.informationProduct.remoteUri}`, '_blank',
        );
      }
    },
    truncateText(text, length) {
      return truncate(text, length);
    },
  },
  data() {
    return {
      // eslint-disable-next-line no-undef
      downloadUrl: `${Routing.generate('pelagos_api_ip_file_download')}`,
    };
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
