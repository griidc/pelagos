<template>
    <div class="m-2">
        <h4 class="text-center">Information Product Form</h4>
        <b-form @submit="onSubmit" @reset="onReset" v-if="show">
            <b-form-group
                    id="input-group-1"
                    label="Title"
                    label-for="title">
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
                    label-for="creators">
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
                    label-for="publisher">
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
                    label-for="externalDoi">
                <b-form-input
                        id="externalDoi"
                        v-model="form.externalDoi"
                        placeholder="Enter DOI"
                ></b-form-input>
            </b-form-group>

            <b-badge
                    variant="secondary"
                    v-for="selectedResearchGroup in form.selectedResearchGroups"
                    v-bind:key="selectedResearchGroup"
                    class="mr-2">
                    {{ getResearchGroupName(selectedResearchGroup) }}
                  <button
                          type="button"
                          class="close"
                          aria-label="Dismiss"
                          v-on:click="removeResearchGroup(selectedResearchGroup)">
                    <span aria-hidden="true">&times;</span>
                  </button>
            </b-badge>

            <input type="hidden" v-model="form.selectedResearchGroups" id="research-groups"/>

            <b-form inline @submit="addResearchGroupLink">
                <label for="add-research-groups" class="pr-2">Find Research Groups:</label>
                <b-form-input
                        v-model="addResearchGroup"
                        list="researchGroupList"
                        id="add-research-groups"
                        class="px-2 mx-2"
                        placeholder="Type to search...">
                </b-form-input>
                <b-form-datalist id="researchGroupList" :options="researchGroupOptions"></b-form-datalist>
                <b-button type="submit" variant="primary">Add</b-button>
            </b-form>

            <b-form-group id="input-group-5" label="Published" label-for="published" v-slot="{ ariaDescribedby }">
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
                    v-slot="{ ariaDescribedby }">
                <b-form-radio-group
                        id="radio-group-2"
                        v-model="form.remoteResource"
                        :options="booleanOptions"
                        :aria-describedby="ariaDescribedby"
                        name="remote-resource-options"
                ></b-form-radio-group>
            </b-form-group>

            <div class="py-2">
                <b-button type="submit" variant="alternate">Submit</b-button>
                <b-button type="reset" variant="dark">Reset</b-button>
            </div>
        </b-form>
        <DxPopup
                :visible.sync="successModal"
                :drag-enabled="false"
                :close-on-outside-click="true"
                :show-title="true"
                :width="400"
                :height="200"
                title="Success!"
                container=".dx-viewport">
            <template>
                <div id="textBlock">
                    <h3>
                        Information Product is Created
                    </h3>
                    <p>
                        Information Product ID: {{ informationProductId }}}
                    </p>
                </div>
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
      successModal: false,
      informationProductId: null,
      addResearchGroup: '',
    };
  },

  methods: {
    onSubmit(event) {
      event.preventDefault();
      postApi(
        // eslint-disable-next-line no-undef
        `${Routing.generate('pelagos_api_create_information_product')}`,
        this.form,
      ).then((response) => {
        this.successModal = true;
        this.informationProductId = response.data.id;
      }).catch((error) => {
        console.log(error);
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
        this.researchGroupOptions.push({
          value: researchGroup.id,
          text: this.$options.filters.truncate(researchGroup.name, 100),
        });
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

    addResearchGroupLink(event) {
      event.preventDefault();
      this.form.selectedResearchGroups.push(this.addResearchGroup);
      const index = this.researchGroupOptions.indexOf(this.addResearchGroup);
      if (index > -1) {
        this.researchGroupOptions.splice(index, 1);
      }
      this.addResearchGroup = '';
    },

    getResearchGroupName(id) {
      let researchGroupName = '';
      window.researchGroups.forEach((researchGroup) => {
        if (Number(id) === Number(researchGroup.id)) {
          researchGroupName = researchGroup.name;
        }
      });
      return this.$options.filters.truncate(researchGroupName, 50);
    },

    removeResearchGroup(id) {
      const index = this.form.selectedResearchGroups.indexOf(id);
      if (index > -1) {
        this.form.selectedResearchGroups.splice(index, 1);
      }
      this.researchGroupOptions.push({
        value: id,
        text: window.researchGroups.find((researchGroup) => Number(id) === researchGroup.id),
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
</style>
