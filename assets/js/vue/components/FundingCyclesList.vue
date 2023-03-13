<template>
    <div class="border p-3 card-product">
        <h4 class="text-center">{{ cardHeadingText }}:</h4>
        <div class="row">
            <div class="col-6 border-right">
                <b-form-group>
                    <label class="col-form-label">
                        Grant Awards
                    </label>
                    <br>
                    <b-form-select v-model="selectedFundingCycle" :options="fundingCycleOptions" class="w-75">
                        <template v-slot:first>
                            <b-form-select-option :value="null" disabled>-- Please select a Grant Award --</b-form-select-option>
                        </template>
                    </b-form-select>
                </b-form-group>
                <label class="col-form-label">
                    Projects
                </label>
                <div class="form-inline">
                    <b-form-select v-model="selectedResearchGroup" :options="researchGroupOptions" :disabled="disableResearchGroups" class="w-75">
                        <template v-slot:first>
                            <b-form-select-option :value="null" disabled>-- Please select a Project --</b-form-select-option>
                        </template>
                    </b-form-select>
                    <b-button class="form-control ml-3" variant="secondary" @click="researchGroupButton" :disabled="disableResGrpBtn">Go</b-button>
                </div>

            </div>
            <div class="col-6">
                <label class="col-form-label">
                    Project Director
                </label>
                <div class="form-inline">
                    <b-form-select v-model="selectedProjectDirector" :options="projectDirectorsOptions" class="w-75">
                        <template v-slot:first>
                            <b-form-select-option :value="null" disabled>-- Please select an associated Project Director --</b-form-select-option>
                        </template>
                    </b-form-select>
                    <b-button class="form-control ml-3" variant="secondary" @click="projectDirectorButton" :disabled="disableProjDirBtn">Go</b-button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import templateSwitch from '@/vue/utils/template-switch';
import { truncate } from '../utils/filters';

export default {
  name: 'FundingCyclesList',
  props: {
    fundingCycles: {
      type: Array,
    },
    projectDirectors: {
      type: Array,
    },
  },
  data() {
    return {
      selectedProjectDirector: null,
      projectDirectorsOptions: [],
      selectedResearchGroup: null,
      researchGroupOptions: [],
      selectedFundingCycle: null,
      fundingCycleOptions: [],
      disableResearchGroups: true,
      disableResGrpBtn: true,
      disableProjDirBtn: true,
      cardHeadingText: templateSwitch.getProperty('cardHeadingText'),
    };
  },
  methods: {
    populateResearchGroups(fundingCycleId) {
      this.researchGroupOptions = [];
      this.selectedResearchGroup = null;
      this.fundingCycles.forEach((fundingCycle) => {
        if (fundingCycle.id === Number(fundingCycleId)) {
          fundingCycle.researchGroups.forEach((researchGroup) => {
            this.researchGroupOptions.push({
              value: researchGroup.id,
              text: truncate(researchGroup.name, 100),
            });
          });
        }
      });
      this.disableResearchGroups = this.researchGroupOptions.length < 1;
    },
    researchGroupButton() {
      this.openResearchGroupLandingPage(this.selectedResearchGroup);
    },
    openResearchGroupLandingPage(researchGroupId) {
      if (researchGroupId) {
        window.open(`/research-group/about/${researchGroupId}`, '_blank');
      }
    },
    populateFundingCycles() {
      this.fundingCycles.forEach((fundingCycle) => {
        this.fundingCycleOptions.push({
          value: fundingCycle.id,
          text: fundingCycle.name,
        });
      });
    },
    projectDirectorButton() {
      this.openResearchGroupLandingPage(this.selectedProjectDirector);
    },
    populateProjectDirectors() {
      this.projectDirectors.forEach((projectDirector) => {
        this.projectDirectorsOptions.push({
          value: projectDirector.researchGroupId,
          text: projectDirector.name,
        });
      });
    },
  },
  created() {
    this.populateFundingCycles();
    this.populateProjectDirectors();
  },
  watch: {
    selectedFundingCycle() {
      this.populateResearchGroups(this.selectedFundingCycle);
    },
    selectedResearchGroup() {
      this.disableResGrpBtn = !this.selectedResearchGroup;
    },
    selectedProjectDirector() {
      this.disableProjDirBtn = !this.selectedProjectDirector;
    },
  },
};
</script>

<style scoped>
    .border-right {
        border-right: 1px solid black;
    }
</style>
