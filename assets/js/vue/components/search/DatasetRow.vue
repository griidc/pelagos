<template>
    <b-card class="card-product" @click="openUrl(url)">
            <div>
                <span class="badge badge-itemtype">Dataset</span>
                <span class="badge badge-identified" v-if="datasetRowData.availabilityStatus === 0">Identified</span>
                <span class="badge badge-submitted" v-else-if="datasetRowData.availabilityStatus === 2 || datasetRowData.availabilityStatus === 4">Submitted</span>
                <span class="badge badge-restricted" v-else-if="datasetRowData.availabilityStatus === 5 || datasetRowData.availabilityStatus === 8">Restricted</span>
                <span class="badge badge-available" v-else-if="datasetRowData.availabilityStatus === 7 || datasetRowData.availabilityStatus === 10">Available</span>
                <span class="badge badge-remotlyhosted" v-if="datasetRowData.availabilityStatus === 7">Remotely Hosted</span>
                <span class="badge badge-coldstorage" v-if="coldStorage()">Cold Storage</span>
                <span class="badge badge-erddap" v-if="datasetRowData.datasetSubmission && datasetRowData.datasetSubmission.erddapUrl">ERDDAP</span>
                <span class="badge badge-ncei" v-if="datasetRowData.datasetSubmission && datasetRowData.datasetSubmission.nceiUrl">NCEI</span>
            </div>
            <b-card-title style="font-size: 1.3rem !important;">{{ datasetRowData.title }}</b-card-title>
            <b-card-text class="d-flex justify-content-between" >
                <div v-if="datasetRowData.datasetSubmission && Object.keys(datasetRowData.datasetSubmission).length > 0">
                    <div v-if="datasetRowData.datasetSubmission.authors" style="max-width: 550px">
                        Authors: {{ datasetRowData.datasetSubmission.authors }}
                    </div>
                    <div v-if="datasetRowData.acceptedDate">
                        Published on: {{ datasetRowData.acceptedDate | localDateTime }}
                    </div>
                    <div v-if="datasetRowData.availabilityStatus !== 7 && fileFormat()">
                        File Format: {{ fileFormat() }}
                    </div>
                </div>
                <div>
                    <div v-if="datasetRowData.availabilityStatus !== 0 && datasetRowData.doi && Object.keys(datasetRowData.doi).length > 0 && datasetRowData.doi.doi">
                        DOI: {{ datasetRowData.doi.doi }}
                    </div>
                    <div>
                        UDI: {{ datasetRowData.udi }}
                    </div>
                    <div v-if="datasetRowData.availabilityStatus !== 7 && fileSize()">
                        File Size: {{ fileSize() }}
                    </div>
                </div>
            </b-card-text>
    </b-card>
</template>

<script>
import xbytes from 'xbytes';

export default {
  name: 'DatasetRow',
  props: {
    datasetRowData: {
      type: Object,
    },
  },
  data() {
    return {
      url: this.datasetRowData.uri,
    };
  },
  methods: {
    openUrl() {
      if (window.getSelection().toString() === '') {
        window.open(`/data/${this.datasetRowData.udi}`, '_blank');
      }
    },
    fileFormat() {
      if (this.datasetRowData.datasetSubmission) {
        return this.datasetRowData.fileFormat ?? this.datasetRowData.datasetSubmission.distributionFormatName;
      }
      return this.datasetRowData.fileFormat ?? null;
    },
    fileSize() {
      if (this.datasetRowData.datasetSubmission) {
        return this.datasetRowData.fileSize ?? xbytes(this.datasetRowData.datasetSubmission.datasetFileSize);
      }
      return this.datasetRowData.fileSize ?? null;
    },
    coldStorage() {
      if (this.datasetRowData.datasetSubmission) {
        return this.datasetRowData.coldStorage ?? this.datasetRowData.datasetSubmission.coldStorage;
      }
      return this.datasetRowData.coldStorage ?? false;
    },
  },
  filters: {
    localDateTime: function (date) {
      return new Date(date).toLocaleTimeString(
        'en-US',
        {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          hour: '2-digit',
          minute: 'numeric',
          hour12: false,
          timeZoneName: 'short'
        }
      );
    }
  },
};
</script>

<style scoped>
    .card-product {
        margin-bottom: 1rem;
        transition: .5s;
    }

    .card-product:hover .btn-overlay {
        opacity: 1;
    }

    .card-product:hover {
        box-shadow: 0 4px 15px rgba(153, 153, 153, 0.3);
        transition: .5s;
        cursor: pointer;
    }
    .card-body {
      padding: 0.625rem !important;
    }
</style>
