<template>
  <span>
    <b-button class="sm rounded-circle" @click="openHelpModal" variant="primary">
      <i class="fa fa-question-circle"></i>
    </b-button>
    <b-modal
        id="modal-1"
        title="Alternative Data Access Methods"
        v-model="showHelpDialog"
        :static="staticValue"
        ok-variant="primary"
        hide-footer
        header-text-variant="dark">
      <div class="modal-body">
        <p>
          Some GRIIDC datasets have been made available via alternative means.
          These methods are provided as a convenience to the user. These methods
          may differ in format or organization from the originally submitted dataset files.
        </p>
        <ul>
          <li><strong>NCEI</strong> - This dataset has been archived with NCEI. Note that this dataset may
            be part of a larger NCEI data package, e.g. one NCEI data package may consist
            of multiple GRIIDC datasets.
          </li>
          &nbsp;
          <li>
            <strong>ERDDAP</strong> - This dataset is also available via the GRIIDC ERDDAP instance.
            ERDDAP is a data server that provides a simple, consistent way to download
            subsets of scientific datasets in common file formats and make graphs and maps.
          </li>
        </ul>
      </div>
    </b-modal>
  </span>
</template>

<script>
export default {
  name: 'HelpModal',
  data() {
    return {
      staticValue: true,
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
