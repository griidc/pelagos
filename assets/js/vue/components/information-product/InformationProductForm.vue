<template>
    <div class="m-2">
        <h4 class="text-center">Information Product Form</h4>
        <b-form @submit="onSubmit" @reset="onReset" v-if="show">
            <b-form-group
                    id="input-group-1"
                    label="Title"
                    label-for="title"
            >
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
            >
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
            >
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
            >
                <b-form-input
                        id="externalDoi"
                        v-model="form.externalDoi"
                        placeholder="Enter DOI"
                ></b-form-input>
            </b-form-group>

            <b-form-group id="input-group-4" label="Research Groups" label-for="research-groups">
                <b-form-input
                        v-model="form.researchGroups"
                        list="researchGroupList"
                        id="research-groups"
                        placeholder="Type to search...">
                </b-form-input>
                <b-form-datalist id="researchGroupList" :options="researchGroupOptions"></b-form-datalist>
            </b-form-group>

            <b-form-group id="input-group-5" label="Published" label-for="published">
                <b-form-select
                        id="published"
                        v-model="form.published"
                        :options="booleanOptions"
                        required
                ></b-form-select>
            </b-form-group>

            <b-form-group id="input-group-6" label="Remote Resource" label-for="remote-resource">
                <b-form-select
                        id="remote-resource"
                        v-model="form.remoteResource"
                        :options="booleanOptions"
                        required
                ></b-form-select>
            </b-form-group>

            <div class="p-2">
                <b-button type="submit" variant="alternate">Submit</b-button>
                <b-button type="reset" variant="dark">Reset</b-button>
            </div>
        </b-form>
    </div>
</template>

<script>
import { postApi } from '@/vue/utils/axiosService';

export default {
  name: 'InformationProductForm',
  data() {
    return {
      form: this.initialFormValues(),
      researchGroupOptions: null,
      booleanOptions: [
        { text: 'Select One', value: null },
        { text: 'Yes', value: true },
        { text: 'No', value: true },
      ],
      show: true,
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
        console.log(response);
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
        researchGroups: null,
        published: null,
        remoteResource: null,
      };
    },
  },

  created() {
    this.populateResearchGroups();
  },
};
</script>

<style scoped>
</style>
