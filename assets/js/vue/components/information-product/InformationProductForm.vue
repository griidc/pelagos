<template>
    <div class="m-2">
        <h4 class="text-center">Information Product Form</h4>
        <b-form @submit="onSubmit" @reset="onReset" v-if="show">
            <b-form-group
                    id="input-group-1"
                    label="Title"
                    label-for="title"
                    description="Brief description about the Information Product">
                <b-form-input
                        id="title"
                        v-model="form.title"
                        placeholder="Enter Title"
                        required
                ></b-form-input>
            </b-form-group>

            <b-form-group
                    id="input-group-2"
                    label="Creators"
                    label-for="creators"
                    description="e.g. John Doe, Jan Doe, etc">
                <b-form-input
                        id="creators"
                        v-model="form.creators"
                        placeholder="Enter Creators"
                        required
                ></b-form-input>
            </b-form-group>

            <b-form-group
                    id="input-group-3"
                    label="Publisher"
                    label-for="publisher"
                    description="e.g. John Doe, Jan Doe, etc">
                <b-form-input
                        id="publisher"
                        v-model="form.publisher"
                        placeholder="Enter Publisher"
                        required
                ></b-form-input>
            </b-form-group>

            <b-form-group
                    id="input-group-4"
                    label="External DOI"
                    label-for="externalDoi"
                    description="e.g. 10.1234/xyz">
                <b-form-input
                        id="externalDoi"
                        v-model="form.externalDoi"
                        placeholder="Enter DOI"
                ></b-form-input>
            </b-form-group>

            <h5>
                <b-badge
                        variant="secondary"
                        v-for="selectedResearchGroup in form.selectedResearchGroups"
                        v-bind:key="selectedResearchGroup"
                        class="mr-2">
                    {{ getResearchGroupShortName(selectedResearchGroup) }}
                    <button
                            type="button"
                            class="close"
                            aria-label="Dismiss"
                            v-on:click="removeResearchGroup(selectedResearchGroup)">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </b-badge>
            </h5>

            <input type="hidden" v-model="form.selectedResearchGroups" id="research-groups"/>
            <p class="alert alert-warning" v-if="!researchGroupsSelected">
              Please select at least one research group!
            </p>

            <b-form-group
                    id="input-group-4"
                    label="Add Research Groups"
                    label-for="add-research-groups"
                    description="Please click the link button to link it to this Information Product">
                <b-form inline>
                    <b-form-input
                            v-model="addedRgShortName"
                            list="researchGroupList"
                            id="add-research-groups"
                            placeholder="Type to search...">
                    </b-form-input>
                    <b-form-datalist id="researchGroupList" :options="researchGroupOptions"></b-form-datalist>
                    <b-button :disabled="isNaN(this.selectedResearchGroup)" type="button" class="ml-2" v-on:click="linkResearchGroup()" >
                      Link Research Group
                    </b-button>
                </b-form>
            </b-form-group>

            <b-form-group
                    id="input-group-file"
                    label="File"
                    label-for="published"
                    description="Upload a file.">
                <div id="dropzone-uploader" class="dropzone" v-bind:class="(form.remoteUri !== '')?'dropzone-uploader-disabled':''">
                </div>
                <p> Filename: {{ fileName }} </p>
                <b-button :disabled="form.remoteUri !== ''" id="upload-file-button" type="button" variant="primary">Upload File</b-button>
            </b-form-group>
            <b-form-group
                    id="input-group-remote-uri"
                    label="Remote URI"
                    label-for="remoteUri"
                    description="Enter the remote URI">
                <b-form-input
                        :disabled="form.file !== ''"
                        id="remoteUri"
                        type="url"
                        v-model="form.remoteUri"
                        placeholder="Enter Remote URI"
                ></b-form-input>
            </b-form-group>

            <b-form-group
                    id="input-group-5"
                    label="Published"
                    label-for="published"
                    v-slot="{ ariaDescribedby }"
                    description="Do you want to publish it?">
                <b-form-radio-group
                        id="radio-group-1"
                        v-model="form.published"
                        :options="booleanOptions"
                        :aria-describedby="ariaDescribedby"
                        name="published-options"
                ></b-form-radio-group>
            </b-form-group>

            <b-form-group
                    id="input-group-6"
                    label="Remote Resource"
                    label-for="remote-resource"
                    v-slot="{ ariaDescribedby }"
                    description="Is it a Remote Resource?">
                <b-form-radio-group
                        id="radio-group-2"
                        v-model="form.remoteResource"
                        :options="booleanOptions"
                        :aria-describedby="ariaDescribedby"
                        name="remote-resource-options"
                ></b-form-radio-group>
            </b-form-group>

            <div class="py-2" v-if="editMode">
                <b-button
                    :disabled="!formValid"
                    type="button"
                    variant="alternate"
                    @click="updateInformationProduct"
                >Update</b-button>
                <b-button type="button" variant="dark" @click="deleteInformationProduct">Delete</b-button>
            </div>
            <div class="py-2" v-else>
              <b-button :disabled="!formValid" type="submit" variant="alternate">Submit</b-button>
              <b-button type="reset" variant="dark">Reset</b-button>
            </div>
        </b-form>
        <DxPopup
                :visible.sync="ipCreatedSuccessModal"
                :drag-enabled="false"
                :close-on-outside-click="true"
                :show-title="true"
                :width="400"
                :height="200"
                title="Success!"
                container=".dx-viewport">
            <template>
                <div>
                    <h6>
                        Information Product is Created
                    </h6>
                    <p>
                        Information Product ID: {{ informationProductId }}
                    </p>
                </div>
            </template>
        </DxPopup>
        <DxPopup
                :visible.sync="errorDialog"
                :drag-enabled="false"
                :close-on-outside-click="true"
                :show-title="true"
                :width="400"
                :height="200"
                title="Error!"
                container=".dx-viewport">
            <template>
                <p>
                    <i class="fas fa-exclamation-triangle fa-2x" style="color:#d9534f"></i>&nbsp;
                    {{ errorMessage }}
                </p>
            </template>
        </DxPopup>
    </div>
</template>

<script>
import { postApi, deleteApi } from '@/vue/utils/axiosService';
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import { DxPopup } from 'devextreme-vue/popup';
import Dropzone from 'dropzone';
import 'dropzone/dist/dropzone.css';

Dropzone.autoDiscover = false;

let thisComponent;

export default {
  name: 'InformationProductForm',
  components: {
    DxPopup,
  },
  data() {
    return {
      form: this.initialFormValues(),
      researchGroupOptions: null,
      booleanOptions: [
        { text: 'Yes', value: true },
        { text: 'No', value: false },
      ],
      show: true,
      ipCreatedSuccessModal: false,
      informationProductId: null,
      errorDialog: false,
      errorMessage: '',
      addedRgShortName: '',
      fileName: 'NO FILE',
      editMode: false,
    };
  },
  computed: {
    researchGroupsSelected() {
      return this.form.selectedResearchGroups.length > 0;
    },
    formValid() {
      return this.researchGroupsSelected
        && this.form.title !== ''
        && this.form.creators !== ''
        && this.form.publisher !== '';
    },
    selectedResearchGroup() {
      return Number(this.getResearchGroupIdFromShortName(this.addedRgShortName));
    },
  },
  mounted() {
    thisComponent = this;
    // eslint-disable-next-line no-use-before-define
    initDropzone();
    if (Object.keys(window.informationProduct).length > 0) {
      this.editMode = true;
      this.populateFormInitialValues();
      console.log('hi');
    }
  },
  methods: {
    onSubmit(event) {
      event.preventDefault();
      postApi(
        // eslint-disable-next-line no-undef
        `${Routing.generate('pelagos_api_create_information_product')}`,
        this.form,
      ).then((response) => {
        this.ipCreatedSuccessModal = true;
        this.informationProductId = response.data.id;
        event.target.reset();
      }).catch(() => {
        this.errorMessage = 'Unable to create Information Product';
        this.errorDialog = true;
      });
    },
    onReset(event) {
      event.preventDefault();
      this.form = this.initialFormValues();
      // Trick to reset/clear native browser form validation state
      this.show = false;
      this.$nextTick(() => {
        this.show = true;
      });
    },
    populateResearchGroups() {
      this.researchGroupOptions = [];
      this.researchGroups = null;
      window.researchGroups.forEach((researchGroup) => {
        this.addToResearchGroupOptions(researchGroup.id);
      });
    },

    initialFormValues() {
      return {
        title: '',
        creators: '',
        publisher: '',
        externalDoi: '',
        selectedResearchGroups: [],
        published: false,
        remoteResource: false,
        file: '',
        remoteUri: '',
      };
    },

    linkResearchGroup() {
      const researchGroupId = this.selectedResearchGroup;
      if (!this.form.selectedResearchGroups.includes(researchGroupId)) {
        this.form.selectedResearchGroups.push(researchGroupId);
        const index = this.researchGroupOptions.findIndex((
          researchGroup,
        ) => Number(researchGroupId) === Number(researchGroup.value));
        if (index > -1) {
          this.researchGroupOptions.splice(index, 1);
        }
      } else {
        this.errorMessage = 'Research Group already linked';
        this.errorDialog = true;
      }
      this.addedRgShortName = '';
    },

    getResearchGroupShortName(id) {
      let researchGroupShortName = '';
      window.researchGroups.forEach((researchGroup) => {
        if (Number(id) === Number(researchGroup.id)) {
          if (researchGroup.shortName) {
            researchGroupShortName = researchGroup.shortName;
          } else {
            researchGroupShortName = this.$options.filters.truncate(researchGroup.name, 50);
          }
        }
      });
      return researchGroupShortName;
    },

    removeResearchGroup(id) {
      const index = this.form.selectedResearchGroups.indexOf(id);
      if (index > -1) {
        this.form.selectedResearchGroups.splice(index, 1);
      }
      this.addToResearchGroupOptions(id);
    },

    getResearchGroupIdFromShortName() {
      let researchGroupId;
      window.researchGroups.forEach((researchGroup) => {
        let researchGroupShortName = '';
        if (researchGroup.shortName) {
          researchGroupShortName = researchGroup.shortName;
        } else {
          researchGroupShortName = this.$options.filters.truncate(researchGroup.name, 50);
        }

        if (this.addedRgShortName === researchGroupShortName) {
          researchGroupId = researchGroup.id;
        }
      });
      return researchGroupId;
    },

    addToResearchGroupOptions(researchGroupId) {
      this.researchGroupOptions.push({
        text: this.getResearchGroupShortName(researchGroupId),
      });
    },

    updateInformationProduct() {},

    deleteInformationProduct() {},

    populateFormInitialValues() {
      this.form.title = window.informationProduct.title;
      this.form.creators = window.informationProduct.creators;
      this.form.publisher = window.informationProduct.publisher;
      this.form.externalDoi = window.informationProduct.externalDoi;
      this.form.selectedResearchGroups = window.informationProduct.researchGroups;
      this.form.published = window.informationProduct.published;
      this.form.remoteResource = window.informationProduct.remoteResource;
      this.form.file = (Object.keys(window.informationProduct.file).length > 0 ? window.informationProduct.file.id : null);
      this.form.remoteUri = window.informationProduct.remoteUri;
      this.fileName = window.informationProduct.file.filePathName;
    },
  },

  created() {
    this.populateResearchGroups();
  },
};

let myDropzone;

const addFileToInformationProduct = (file, done) => {
  const currentFile = file;
  let fileName = '';
  if (currentFile.fullPath) {
    fileName += currentFile.fullPath ?? currentFile.name;
  } else if (currentFile.webkitRelativePath) {
    fileName += currentFile.webkitRelativePath;
  } else {
    fileName += currentFile.name;
  }
  const chunkData = {};
  chunkData.dzuuid = file.upload.uuid;
  chunkData.dztotalchunkcount = file.upload.totalChunkCount;
  chunkData.fileName = fileName;
  chunkData.dztotalfilesize = file.size;
  postApi(
    // eslint-disable-next-line no-undef
    `${Routing.generate('pelagos_api_add_file_information_product')}`,
    chunkData,
  ).then((response) => {
    const fileId = response.data.id;
    // eslint-disable-next-line no-param-reassign
    file.fileId = fileId;
    thisComponent.form.file = fileId;
    thisComponent.fileName = fileName;

    done();
  }).catch((error) => {
    // eslint-disable-next-line no-param-reassign
    file.accepted = false;
    // eslint-disable-next-line no-underscore-dangle
    myDropzone._errorProcessing([file], error.response.data, error.request);
  });
};

const initDropzone = () => {
  myDropzone = new Dropzone('div#dropzone-uploader', {
    // eslint-disable-next-line no-undef
    url: `${Routing.generate('pelagos_api_post_chunks')}`,
    chunking: true,
    chunkSize: 1024 * 1024 * 10,
    forceChunking: true,
    parallelChunkUploads: false,
    parallelUploads: 1,
    retryChunks: true,
    retryChunksLimit: 3,
    maxFilesize: null,
    clickable: '#upload-file-button',
    timeout: 0,
    addRemoveLinks: true,
    accept(file, done) {
      if (myDropzone.getAcceptedFiles().length > 0) {
        file.previewElement.remove();
        thisComponent.errorMessage = 'Only one file allowed!';
        thisComponent.errorDialog = true;
        done('Only one file allowed!');
      } else {
        done();
      }
    },
    removedfile: (file) => {
      if (typeof file.fileId !== 'undefined') {
        deleteApi(
          // eslint-disable-next-line no-undef
          `${Routing.generate('pelagos_api_ip_file_delete')}/${file.fileId}`,
        ).then(() => {
          thisComponent.form.file = '';
        }).catch(() => {
          thisComponent.errorMessage = 'Unable to delete File from Information Product';
          thisComponent.errorDialog = true;
        });
      }
      file.previewElement.remove();
    },
    error: function error() {
      thisComponent.errorMessage = 'Unable to save file.';
      thisComponent.errorDialog = true;
    },
    chunksUploaded(file, done) {
      // All chunks have been uploaded. Perform any other actions
      addFileToInformationProduct(file, done);
    },
  });
  if (Object.keys(window.informationProduct).length > 0) {
    if (Object.keys(window.informationProduct.file).length > 0) {
      myDropzone.on('addedfile', (file) => {
        if (typeof file.fileId !== 'undefined') {
          thisComponent.form.file = file.fileId;
        }
      });

      const existingFile = {
        name: window.informationProduct.file.filePathName,
        size: window.informationProduct.file.fileSize,
        fileId: window.informationProduct.file.fileId,
      };

      myDropzone.emit('addedfile', existingFile);
      myDropzone.emit('complete', existingFile);
    }
  }
};
</script>

<style scoped lang="scss">
.badge {
    .close {
    margin-left: .25rem;
    color: inherit;
    font-size: 100%;
    text-shadow: 0 1px 0 rgba(#000, .5);
    }
}
#add-research-groups {
  width: 75%;
}
.dropzone-uploader-disabled {
  pointer-events: none;
  cursor: default;
  opacity: 50%;
  background-color:lightgray;
}
</style>
