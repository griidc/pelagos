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

            <div class="py-2">
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
import { postApi } from '@/vue/utils/axiosService';
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import { DxPopup } from 'devextreme-vue/popup';

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
  },

  created() {
    this.populateResearchGroups();
  },
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
</style>
