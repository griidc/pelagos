<template>
    <div>
        <b-button size="sm" class="mb-2" @click="downloadBtn" variant="primary">
          <i class="fas fa-external-link-alt" v-if="datasetInfo.remotelyHosted"></i>
          <i class="fas fa-download" v-else></i>
          {{ buttonTitle }}
        </b-button>
        <DxPopup
            :title="buttonTitle"
            :visible.sync="showDownloadDialog"
            :close-on-outside-click="true"
            :show-title="true"
            :width="500"
            :height="500">
            <template>
                <div v-if="datasetInfo.remotelyHosted">
                    <h3>The dataset you selected is hosted by an external repository.</h3>
                    <div>
                        <p>
                            All materials on this website are made available to
                            GRIIDC and in turn to you "as-is." By downloading files,
                            you agree to the <a href=https://data.gulfresearchinitiative.org/terms-and-conditions>GRIIDC Terms of
                            Service</a>.
                        </p>
                        <p>
                            This particular dataset is not hosted directly by GRIIDC, so additional
                            terms and conditions may be
                            imposed by the hosting entity.
                        </p>
                    </div>
                    <div>
                        <strong>UDI:</strong> {{ datasetInfo.dataset.udi }}<br>
                        <strong>Location:</strong>
                        <a :href=datasetInfo.fileUri target=_BLANK>
                            {{ datasetInfo.fileUri }}
                        </a>
                        <b-icon
                                icon="arrow-up-right"
                                aria-hidden="true"
                                v-if="datasetInfo.remotelyHosted">
                        </b-icon>
                        <br>
                        <p>
                            {{ additionalInfo }}
                        </p>
                    </div>
                </div>
                <div v-else>
                    <div>
                        <p>
                            All materials on this website are made available to GRIIDC and in turn
                            to you "as-is." By downloading files, you agree to the
                            <a href="https://data.gulfresearchinitiative.org/terms-and-conditions" target="_blank">
                                GRIIDC Terms of Service
                            </a>
                        </p>
                    </div>
                    <div v-show="this.datasetInfo.dataset.coldStorage">
                      <hr>
                      <p>
                        This dataset has been put into cold storage due to its large size. The files below are manifests describing
                        the dataset files and directory structure.  In order to obtain this dataset, please email <a href="mailto:griidc@gomri.org">griidc@gomri.org</a>
                        to make arrangements. If you would like a subset of the dataset files, please indicate which directories and/or files.
                      </p>
                    </div>
                    <hr>
                    <div>
                        <strong>UDI:</strong> {{ datasetInfo.dataset.udi }}<br>
                        <strong>File name:</strong> {{ datasetInfo.dataset.filename }}<br>
                        <strong>File size:</strong> {{ datasetInfo.dataset.fileSize }}<br>
                        <strong>SHA256 Checksum:</strong> {{ datasetInfo.dataset.checksum }}<br>
                        <strong>Estimated Download Time:</strong> {{ estimatedDownloadTime }}<br>
                    </div>
                </div>
                <DxToolbarItem v-if="!datasetInfo.remotelyHosted"
                               widget="dxButton"
                               toolbar="bottom"
                               location="center"
                               :options="downloadButtonOptions"
                />
            </template>
        </DxPopup>
    </div>
</template>

<script>
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import { DxPopup, DxToolbarItem } from 'devextreme-vue/popup';
import { getApi } from '../../utils/axiosService';

export default {
  name: 'DownloadZipBtn',
  components: {
    DxPopup,
    DxToolbarItem,
  },
  props: {
    id: {
      required: true,
    },
  },
  data() {
    return {
      showDownloadDialog: false,
      datasetInfo: {
        remotelyHosted: false,
        dataset: {},
        fileUri: '',
      },
      buttonTitle: '',
      additionalInfo: '',
      downloadButtonOptions: {
        icon: 'download',
        text: 'Download',
        onClick: () => {
          // eslint-disable-next-line no-undef
          const url = `${Routing.generate('pelagos_app_download_dataset')}/${this.id}`;
          const link = document.createElement('a');
          link.href = url;
          document.body.appendChild(link);
          setTimeout(() => {
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
          }, 0);
          link.click();
          // eslint-disable-next-line no-undef
          // notify({
          //   // eslint-disable-next-line no-undef
          //   message,
          //   position: {
          //     my: 'center top',
          //     at: 'center top',
          //   },
          // }, 'success', 3000);
        },
      },
      estimatedDownloadTime: 'Calculating...',
    };
  },
  created() {
    // eslint-disable-next-line no-undef
    getApi(Routing.generate('pelagos_app_download_default', { id: this.id })).then((response) => {
      this.datasetInfo = response.data;
      if (this.datasetInfo.remotelyHosted) {
        this.buttonTitle = 'External Dataset Link';
        if (this.datasetInfo.dataset.availability === 5) {
          this.additionalInfo = `This dataset is restricted for download but is hosted by another
                    website so availability status is not guaranteed to be accurate. To obtain access to this dataset,
                    please click the location link above and follow any instructions provided.`;
        } else {
          this.additionalInfo = `To download this dataset, please use the location link above.
                    Note, this dataset is not hosted at GRIIDC; the site is not under GRIIDC control and
                    GRIIDC is not responsible for the information or links you may find there.`;
        }
      } else {
        this.buttonTitle = this.datasetInfo.dataset.coldStorage ? 'Download File Manifest Zip' : 'Download Zip';
      }
    });
  },
  methods: {
    downloadBtn() {
      this.showDownloadDialog = true;
    },
  },
  watch: {
    showDownloadDialog() {
      if (this.showDownloadDialog === true) {
        const start = new Date().getTime();
        // eslint-disable-next-line no-undef
        getApi(`${Routing.getBaseUrl()}/testfile.bin?id=${start}`)
          .then((response) => {
            const end = new Date().getTime();
            const diff = (end - start) / 1000;
            const bytes = response.headers['content-length'];
            const speed = (bytes / diff);
            let time = this.datasetInfo.dataset.fileSizeRaw / speed;
            let unit = 'second';
            if (time > 60) {
              time /= 60;
              unit = 'minute';
            }
            if (time > 60) {
              time /= 60;
              unit = 'hour';
            }
            if (Math.round(time) !== 1) unit += 's';
            this.estimatedDownloadTime = `${Math.round(time)}${unit} (based on your current connection speed)`;
          });
      }
    },
  },
};
</script>

<style scoped>

</style>
