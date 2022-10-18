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
      :width="500"
      :height="500"
    >
      <template>
        <div>
          <p>
            Some GRIIDC datasets have been made available via alternative means.
            These methods are provided as a convenience to the user. These
            methods may differ in format or organization from the originally
            submitted dataset files.
          </p>
          <ul>
            <li>
              <strong>NCEI</strong> - This dataset has been archived with NCEI.
              Note that this dataset may be part of a larger NCEI data package,
              e.g. one NCEI data package may consist of multiple GRIIDC
              datasets.
            </li>
            &nbsp;
            <li>
              <strong>ERDDAP</strong> - This dataset is also available via the
              GRIIDC ERDDAP instance. ERDDAP is a data server that provides a
              simple, consistent way to download subsets of scientific datasets
              in common file formats and make graphs and maps.
            </li>
          </ul>
        </div>
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
  data() {
    return {
      showHelpDialog: false,
      helpBtnTitle: 'Alternative Data Access Methods',
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
