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
                        placeholder="Enter title"
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
                    label-for="doi"
            >
                <b-form-input
                        id="doi"
                        v-model="form.doi"
                        placeholder="Enter DOI"
                ></b-form-input>
            </b-form-group>

            <b-form-group id="input-group-4" label="Research Groups" label-for="research-groups">
                <b-form-select
                        id="research-groups"
                        v-model="form.selectedResearchGroup"
                        multiple
                        :options="researchGroupOptions"
                        required
                >
                    <template v-slot:first>
                        <b-form-select-option
                                :value="null"
                                disabled
                        >
                            -- Please select a Research Group --
                        </b-form-select-option>
                    </template>
                </b-form-select>
            </b-form-group>

            <b-form-group id="input-group-5" label="Published" label-for="publish">
                <b-form-select
                        id="publish"
                        v-model="form.publish"
                        :options="booleanOptions"
                        required
                ></b-form-select>
            </b-form-group>

            <b-form-group id="input-group-6" label="Remote Resource" label-for="remote-resource">
                <b-form-select
                        id="remote-resource"
                        v-model="form.remote"
                        :options="booleanOptions"
                        required
                ></b-form-select>
            </b-form-group>

            <b-button type="submit" variant="primary">Submit</b-button>
            <b-button type="reset" variant="danger">Reset</b-button>
        </b-form>
    </div>
</template>

<script>
export default {
  name: 'InformationProductForm',
  data() {
    return {
      form: {
        title: '',
        creators: '',
        publisher: '',
        doi: '',
        selectedResearchGroup: [],
        publish: null,
        remote: null,
      },
      researchGroupOptions: [],
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
      alert(JSON.stringify(this.form));
    },
    onReset(event) {
      event.preventDefault();
    },
    populateResearchGroups() {
      this.researchGroupOptions = [];
      this.selectedResearchGroup = null;
      window.researchGroups.forEach((researchGroup) => {
        this.researchGroupOptions.push({
          value: researchGroup.id,
          text: this.$options.filters.truncate(researchGroup.name, 100),
        });
      });
    },
  },

  created() {
    this.populateResearchGroups();
  },
};
</script>

<style scoped>

</style>
