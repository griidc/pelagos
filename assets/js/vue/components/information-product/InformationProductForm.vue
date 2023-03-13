<template>
  <div class="m-2 pb-5">
    <h4 class="text-center">Information Product Form</h4>
    <b-form @submit="onSubmit" @reset="onReset" v-if="show">
      <p v-if="editMode"> Information Product ID: {{ informationProductId }} </p>
      <b-form-group
          id="input-group-title"
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
          id="input-group-creators"
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
          id="input-group-publisher"
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
          id="input-group-doi"
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
          id="input-group-researchgroup"
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
          <b-button :disabled="isNaN(this.selectedResearchGroup)" type="button" class="ml-2"
                    v-on:click="linkResearchGroup()">
            Link Research Group
          </b-button>
        </b-form>
      </b-form-group>

      <b-form-group
          id="input-group-file"
          label="File"
          label-for="published"
          description="Upload a file.">
        <div id="dropzone-uploader" class="dropzone" v-bind:class="(form.remoteUri)?'dropzone-uploader-disabled':''">
        </div>
        <b-button :disabled="!!form.remoteUri" id="upload-file-button" type="button" variant="primary">Upload File
        </b-button>
      </b-form-group>

      <b-form-group
          id="input-group-remote-uri"
          label="Remote URI"
          label-for="remoteUri"
          description="Enter the remote URI">
        <b-form-input
            :disabled="!!form.file"
            id="remoteUri"
            type="url"
            v-model="form.remoteUri"
            placeholder="Enter Remote URI"
        ></b-form-input>
      </b-form-group>

      <b-form-group
          id="input-group-published"
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
          id="input-group-remoteresource"
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

      <input type="hidden" v-model="form.selectedProductTypes" id="product-types"/>
      <p class="alert alert-warning" v-if="!productTypesSelected">
        Please select at least one product type!
      </p>
      <b-form-group
          id="input-group-producttype"
          label="Product Type Descriptor"
          label-for="product-type"
          description="Can add multiple types">
        <DxTagBox
            :data-source="productTypeOptions"
            :value="productValue"
            display-expr="description"
            value-expr="id"
            :search-enabled="true"
            @selectionChanged="onProductTypeSelection"
        />
      </b-form-group>

      <input type="hidden" v-model="form.selectedDigitalResourceTypes" id="digital-resource-types"/>
      <p class="alert alert-warning" v-if="!digitalResourceTypesSelected">
        Please select at least one digital resource type!
      </p>
      <b-form-group
          id="input-group-digitalresourcetype"
          label="Digital Resource Type Descriptor"
          label-for="digital-resource-type"
          description="Can add multiple types">
        <DxTagBox
            :data-source="digitalResourceTypeOptions"
            :value="digitalResourceValue"
            display-expr="description"
            value-expr="id"
            :search-enabled="true"
            @selectionChanged="onDigitalResourceTypeSelection"
        />
      </b-form-group>

      <div class="py-2">
        <b-button :disabled="!formValid" type="submit" variant="alternate">{{ submitBtnText }}</b-button>
        <b-button v-if="!editMode" type="reset" variant="dark">Reset</b-button>
        <b-button v-if="editMode" type="button" variant="danger" @click="showDeleteDialog">Delete</b-button>
      </div>
    </b-form>
    <DxPopup
        :visible="ipCreatedSuccessModal"
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
            {{ ipSuccessModalText }}
          </h6>
          <p>
            Information Product ID: {{ informationProductId }}
          </p>
        </div>
      </template>
    </DxPopup>
    <DxPopup
        :visible="errorDialog"
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
    <DxPopup
        :visible="deleteConfirmationDialog"
        :close-on-outside-click="false"
        :show-title="false"
        position="center"
        :showCloseButton="false"
        :width="350"
        height="auto"
        :drag-enabled="false"
        :shading="true"
        shading-color="rgba(0,0,0,0.4)"
    >
      <template>
        <div class="confirmation-dialog">
          <p>
            <b>Do you really want to delete this Information Product?</b>
          </p>
          <br>
          <DxButton
              text="Yes"
              type="danger"
              width="50%"
              styling-mode="contained"
              @click="deleteInformationProduct"
          />
          <DxButton
              text="No"
              width="50%"
              styling-mode="contained"
              @click="cancelDelete"
          />
        </div>
      </template>
    </DxPopup>
  </div>
</template>

<script>
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import { DxPopup } from 'devextreme-vue/popup';
import { DxButton } from 'devextreme-vue/button';
import Dropzone from 'dropzone';
import 'dropzone/dist/dropzone.css';
import DxTagBox from 'devextreme-vue/tag-box';
import DataSource from 'devextreme/data/data_source';
import { postApi, deleteApi, patchApi } from '../../utils/axiosService';
import { truncate } from '../../utils/filters';

Dropzone.autoDiscover = false;

let thisComponent;

export default {
  name: 'InformationProductForm',
  components: {
    DxPopup,
    DxButton,
    DxTagBox,
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
      fileName: '',
      editMode: false,
      submitBtnText: 'Submit',
      deleteConfirmationDialog: false,
      ipSuccessModalText: '',
      selectedProductTypeDescriptions: [],
      productTypeOptions: new DataSource({
        store: window.productTypeDescriptors,
        key: 'id',
      }),
      productValue: [],
      digitalResourceTypeOptions: new DataSource({
        store: window.digitalResourceTypeDescriptors,
        key: 'id',
      }),
      digitalResourceValue: [],
    };
  },
  computed: {
    researchGroupsSelected() {
      return this.form.selectedResearchGroups.length > 0;
    },
    formValid() {
      return this.digitalResourceTypesSelected
          && this.productTypesSelected
          && this.researchGroupsSelected
          && this.form.title !== ''
          && this.form.creators !== ''
          && this.form.publisher !== '';
    },
    selectedResearchGroup() {
      return Number(this.getResearchGroupIdFromShortName(this.addedRgShortName));
    },
    productTypesSelected() {
      return this.productValue.length > 0;
    },
    digitalResourceTypesSelected() {
      return this.digitalResourceValue.length > 0;
    },
  },
  mounted() {
    thisComponent = this;
    // eslint-disable-next-line no-use-before-define
    initDropzone();
    if ((typeof window.informationProduct === 'object' && window.informationProduct !== null)) {
      this.editMode = true;
      this.populateFormInitialValues();
      this.informationProductId = window.informationProduct.id;
      this.submitBtnText = 'Save Changes';
      this.productValue = this.getProductTypeDescriptorIds();
      this.digitalResourceValue = this.getDigitalResourceTypeDescriptorIds();
    }
  },
  methods: {
    onSubmit(event) {
      event.preventDefault();
      this.form.selectedProductTypes = this.productValue;
      this.form.selectedDigitalResourceTypes = this.digitalResourceValue;
      if (this.editMode) {
        patchApi(
          // eslint-disable-next-line no-undef
          `${Routing.generate('pelagos_api_update_information_product')}/${this.informationProductId}`,
          this.form,
        ).then(() => {
          this.ipSuccessModalText = 'Information Product is Updated!';
          this.ipCreatedSuccessModal = true;
        }).catch((error) => {
          this.errorMessage = `Unable to update Information Product as ${error.response.data.message}`;
          this.errorDialog = true;
        });
      } else {
        postApi(
          // eslint-disable-next-line no-undef
          `${Routing.generate('pelagos_api_create_information_product')}`,
          this.form,
        ).then((response) => {
          this.ipSuccessModalText = 'Information Product is Created!';
          this.ipCreatedSuccessModal = true;
          this.informationProductId = response.data.id;
          event.target.reset();
        }).catch((error) => {
          this.errorMessage = `Unable to create Information Product as ${error.response.data.message}`;
          this.errorDialog = true;
        });
      }
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
        selectedProductTypes: [],
        selectedDigitalResourceTypes: [],
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
            researchGroupShortName = truncate(researchGroup.name, 50);
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
          researchGroupShortName = truncate(researchGroup.name, 50);
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

    deleteInformationProduct() {
      deleteApi(
        // eslint-disable-next-line no-undef
        `${Routing.generate('pelagos_api_delete_information_product')}/${this.informationProductId}`,
      ).then(() => {
        // eslint-disable-next-line no-undef
        window.open(`${Routing.generate('pelagos_app_ui_information_product')}`, '_self');
      }).catch(() => {
        thisComponent.errorMessage = 'Unable to delete Information Product';
        thisComponent.errorDialog = true;
      });
    },

    populateFormInitialValues() {
      this.form.title = window.informationProduct.title;
      this.form.creators = window.informationProduct.creators;
      this.form.publisher = window.informationProduct.publisher;
      this.form.externalDoi = window.informationProduct.externalDoi;
      this.form.selectedResearchGroups = window.informationProduct.researchGroups;
      this.form.selectedProductTypes = this.getProductTypeDescriptorIds();
      this.form.selectedDigitalResourceTypes = this.getDigitalResourceTypeDescriptorIds();
      this.form.published = window.informationProduct.published;
      this.form.remoteResource = window.informationProduct.remoteResource;
      this.form.file = (typeof window.informationProduct.file === 'object' && window.informationProduct.file !== null) ? window.informationProduct.file.id : null;
      this.form.remoteUri = window.informationProduct.remoteUri;
      this.fileName = (typeof window.informationProduct.file === 'object' && window.informationProduct.file !== null) ? window.informationProduct.file.filePathName : null;
    },

    showDeleteDialog() {
      this.deleteConfirmationDialog = true;
    },

    cancelDelete() {
      this.deleteConfirmationDialog = false;
    },

    onProductTypeSelection(event) {
      event.addedItems.forEach((value) => {
        if (!this.productValue.includes((value.id))) {
          this.productValue.push(value.id);
        }
      });

      event.removedItems.forEach((value) => {
        const index = this.productValue.indexOf(value.id);
        if (index > -1) {
          this.productValue.splice(index, 1);
        }
      });
    },

    getProductTypeDescriptorIds() {
      const productTypeDescriptorIds = [];
      window.informationProduct.productTypeDescriptors.forEach((productTypeDescriptor) => {
        productTypeDescriptorIds.push(productTypeDescriptor.id);
      });
      return productTypeDescriptorIds;
    },

    onDigitalResourceTypeSelection(event) {
      event.addedItems.forEach((value) => {
        if (!this.digitalResourceValue.includes((value.id))) {
          this.digitalResourceValue.push(value.id);
        }
      });

      event.removedItems.forEach((value) => {
        const index = this.digitalResourceValue.indexOf(value.id);
        if (index > -1) {
          this.digitalResourceValue.splice(index, 1);
        }
      });
    },

    getDigitalResourceTypeDescriptorIds() {
      const digitalResourceTypeDescriptorIds = [];
      window.informationProduct.digitalResourceTypeDescriptors.forEach((digitalResourceTypeDescriptor) => {
        digitalResourceTypeDescriptorIds.push(digitalResourceTypeDescriptor.id);
      });
      return digitalResourceTypeDescriptorIds;
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
  chunkData.informationProductId = thisComponent.informationProductId;
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
          `${Routing.generate('pelagos_api_ip_file_delete')}?informationProductId=${thisComponent.informationProductId}&fileId=${thisComponent.form.file}`,
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

  if (typeof window.informationProduct === 'object' && window.informationProduct !== null) {
    if (typeof window.informationProduct.file === 'object' && window.informationProduct.file !== null) {
      myDropzone.on('addedfile', (file) => {
        if (typeof file.fileId !== 'undefined') {
          thisComponent.form.file = file.fileId;
        }
      });

      const existingFile = {
        name: window.informationProduct.file.filePathName,
        size: window.informationProduct.file.fileSize,
        fileId: window.informationProduct.file.id,
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
.confirmation-dialog {
  align-items: center;
  text-align: center;
}
</style>
