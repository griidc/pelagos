<template>
  <span>
    <b-button
      class="btn-sm rounded-circle border-0"
      @click="openHelpModal"
      variant="light"
    >
      <i class="far fa-question-circle"></i>
    </b-button>
    <DxPopup
      :title="helpBtnTitle"
      :visible.sync="showHelpDialog"
      :close-on-outside-click="true"
      :show-title="true"
      :width="width"
      :height="height"
    >
      <template>
        <slot></slot>
      </template>
    </DxPopup>
  </span>
</template>

<script>
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import { DxPopup } from 'devextreme-vue/popup';

export default {
  name: 'HelpModal',
  components: {
    DxPopup,
  },
  props: {
    helpBtnTitle: {
      type: String,
    },
    width: {
      type: Number,
    },
    height: {
      type: Number,
    },
  },
  data() {
    return {
      showHelpDialog: false,
    };
  },
  methods: {
    openHelpModal() {
      this.showHelpDialog = true;
    },
  },
};
</script>

<style scoped>
</style>
