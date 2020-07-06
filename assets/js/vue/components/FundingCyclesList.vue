<template>
    <div class="row">
        <div class="col-6 border-right">
            <label class="col-form-label-lg">
                Funding Cycles
            </label>
            <b-form-select v-model="selectedFundingCycle" :options="fundingCycleOptions" class="w-75"></b-form-select>

            <label class="col-form-label-lg">
                Research Groups
            </label>
            <div class="form-inline">
                <b-form-select v-model="selectedResearchGroup" :options="researchGroupOptions" :disabled="disableResearchGroups" class="w-75"></b-form-select>
                <b-button class="form-control ml-3" variant="primary" @click="researchGroupButton" :disabled="disableResGrpBtn">Go</b-button>
            </div>

        </div>
        <div class="col-6">
            <label class="col-form-label-lg">
                    Search for Research Group Page By
            </label>
            <div class="form-inline">
                <b-form-select v-model="selectedProjectDirector" :options="projectDirectorsOptions"></b-form-select>
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
                projectDirectorsOptions: [{ value: null, text: '[Please select an associated Project Director]' }],
                selectedResearchGroup: null,
                researchGroupOptions: [{ value: null, text: '[Please select a Research Group]' }],
                selectedFundingCycle: null,
                fundingCycleOptions: [{ value: null, text: '[Please select a Funding Cycle]' }],
                disableResearchGroups: true,
                disableResGrpBtn: true,
                disableProjDirBtn: true,
            }
        },
        methods: {
            populateResearchGroups: function(fundingCycleId) {
                this.researchGroupOptions = [{ value: null, text: '[Please select a Research Group]' }];
                this.fundingCycles.forEach(fundingCycle => {
                    if (fundingCycle.id === Number(fundingCycleId)) {
                        fundingCycle.researchGroups.forEach(researchGroup => {
                            this.researchGroupOptions.push({
                                value: researchGroup.id,
                                text: this.$options.filters.truncate(researchGroup.name, 50)
                            })
                        })
                    }
                })
                this.disableResearchGroups = false;
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
        },
        filters: {
            truncate: function (text, length) {
                let regex = new RegExp('^.{' + length + '}\\S*');
                let split = text.match(regex);
                return (split ? split[0] + '...' : text);
            },
        }
    };
</script>

<style scoped>
    .border-right {
        border-right: 1px solid black;
    }
</style>
