<template>
    <div class="row">
        <div class="col-6 border-right">
            <label class="col-form-label-lg">
                Funding Cycles
            </label>
            <b-form-select v-model="selectedFundingCycle" :options="fundingCycleOptions" class="w-75">
                <template v-slot:first>
                    <b-form-select-option :value="null" disabled>-- Please select a Funding Cycle --</b-form-select-option>
                </template>
            </b-form-select>

            <label class="col-form-label-lg">
                Research Groups
            </label>
            <div class="form-inline">
                <b-form-select v-model="selectedResearchGroup" :options="researchGroupOptions" :disabled="disableResearchGroups" class="w-75">
                    <template v-slot:first>
                        <b-form-select-option :value="null" disabled>-- Please select a Research Group --</b-form-select-option>
                    </template>
                </b-form-select>
                <b-button class="form-control ml-3" variant="primary" @click="researchGroupButton" :disabled="disableResGrpBtn">Go</b-button>
            </div>

        </div>
        <div class="col-6">
            <label class="col-form-label-lg">
                    Search for Research Group Page By
            </label>
            <div class="form-inline">
                <b-form-select v-model="selectedProjectDirector" :options="projectDirectorsOptions" class="w-75">
                    <template v-slot:first>
                        <b-form-select-option :value="null" disabled>-- Please select an associated Project Director --</b-form-select-option>
                    </template>
                </b-form-select>
                <b-button class="form-control ml-3" variant="primary" @click="projectDirectorButton" :disabled="disableProjDirBtn">Go</b-button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "FundingCyclesList",
        props: {
            fundingCycles: {
                type: Array,
            },
            projectDirectors: {
                type: Array
            }
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
            }
        },
        methods: {
            populateResearchGroups: function(fundingCycleId) {
                this.researchGroupOptions = [];
                this.selectedResearchGroup = null;
                this.fundingCycles.forEach(fundingCycle => {
                    if (fundingCycle.id === Number(fundingCycleId)) {
                        fundingCycle.researchGroups.forEach(researchGroup => {
                            this.researchGroupOptions.push({
                                value: researchGroup.id,
                                text: this.$options.filters.truncate(researchGroup.name, 100)
                            })
                        })
                    }
                })
                this.disableResearchGroups = this.researchGroupOptions.length <= 1;
            },
            researchGroupButton: function() {
                this.openResearchGroupLandingPage(this.selectedResearchGroup);
            },
            openResearchGroupLandingPage: function (researchGroupId) {
                if (researchGroupId) {
                    window.open("/research-group/about/" + researchGroupId, '_blank');
                }
            },
            populateFundingCycles: function () {
                this.fundingCycles.forEach(fundingCycle => {
                    this.fundingCycleOptions.push({
                        value: fundingCycle.id,
                        text: fundingCycle.name
                    })
                })
            },
            projectDirectorButton: function () {
                this.openResearchGroupLandingPage(this.selectedProjectDirector);
            },
            populateProjectDirectors: function () {
                this.projectDirectors.forEach(projectDirector => {
                    this.projectDirectorsOptions.push({
                        value: projectDirector.researchGroupId,
                        text: projectDirector.name
                    })
                })
            }
        },
        created() {
            this.populateFundingCycles();
            this.populateProjectDirectors();
        },
        watch: {
            selectedFundingCycle: function () {
                this.populateResearchGroups(this.selectedFundingCycle);
            },
            selectedResearchGroup: function () {
                this.disableResGrpBtn = !this.selectedResearchGroup;
            },
            selectedProjectDirector: function () {
                this.disableProjDirBtn = !this.selectedProjectDirector;
            }
        }
    };
</script>

<style scoped>
    .border-right {
        border-right: 1px solid black;
    }
</style>
