<template>
    <b-card class="mb-3">
        <b-card-body>
            <div class="row">
                <div class="col-sm-3">
                    <h6 class="mb-0">Full Name</h6>
                </div>
                <div class="col-sm-9 text-secondary">
                    {{ `${personDetails.firstName}  ${personDetails.lastName}` }}
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-sm-3">
                    <h6 class="mb-0">Email</h6>
                </div>
                <div class="col-sm-9 text-secondary">
                    {{ personDetails.emailAddress }}
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-sm-3">
                    <h6 class="mb-0">Phone</h6>
                </div>
                <div class="col-sm-9 text-secondary">
                    {{ personDetails.phoneNumber }}
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-sm-3">
                    <h6 class="mb-0">Address</h6>
                </div>
                <div class="col-sm-9 text-secondary">
                    {{ `${personDetails.organization},
                        ${personDetails.city},
                        ${personDetails.administrativeArea} - ${personDetails.postalCode},
                        ${personDetails.country}` }}
                </div>
            </div>
            <hr>
            <div class="row"  v-show="personDetails.isMe">
                <div class="col-sm-3">
                    <h6 class="mb-0">GridFTP</h6>
                </div>
                <div class="col-sm-9 text-secondary" v-if="personDetails.isPosix">
                    This account has Globus/SFTP enabled. Your username is <i>{{ personDetails.posixUsername }}</i>
                </div>
                <div class="col-sm-9 text-secondary" v-else>
                    Globus/SFTP is not currently enabled on this account.
                    <b-button variant="primary" @click="requestposix"><b-spinner small v-if="loading"></b-spinner>
                        Request Globus/SFTP Access
                    </b-button>
                </div>
            </div>
            <hr v-show="personDetails.isMe">
            <div class="row"  v-show="personDetails.isMe">
                <div class="col-sm-3">
                    <h6 class="mb-0">Details</h6>
                </div>
                <div class="col-sm-9 text-secondary">
                    If enabled, this option enables use of griidc-ingest.griidc.org globus or SFTP endpoint
                    to upload large files (including multi TB files) to GRIIDC.
                </div>
            </div>
        </b-card-body>
    </b-card>
</template>

<script>
import { patchApi } from '@/vue/utils/axiosService';

export default {
  name: 'UserDetailsCard',
  props: {
    personDetails: {
      type: Object,
    },
  },
  data() {
    return {
      loading: false,
    };
  },
  methods: {
    requestposix() {
      this.loading = true;
      patchApi(
        // eslint-disable-next-line no-undef
        Routing.generate('pelagos_api_account_make_posix'),
        {},
      ).finally(() => {
        this.$emit('loadPersonData');
        this.loading = false;
      });
    },
    getHostName() {
      // eslint-disable-next-line no-undef
      return Routing.getBaseUrl();
    },
  },
};
</script>

<style scoped>

</style>
