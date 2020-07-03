<template>
    <div class="row">
        <div class="col-6 border-right">
            <label class="col-form-label-lg">
                Funding Cycles
            </label>

            <select @change="populateResearchGroups" class="form-control">
                <option value="" selected>[Please select a Funding Cycle]</option>
                <option v-for="fundingCycle in fundingCycles" :value="fundingCycle.id" :key="fundingCycle.id">{{ fundingCycle.name }}</option>
            </select>

            <label class="col-form-label-lg">
                Research Groups
            </label>

            <select @change="selectResearchGroup" class="form-control" :disabled="researchGroupDisabled">
                <option value="" selected>[Please select a Research Groups]</option>
                <option v-for="researchGroup in researchGroups" :value="researchGroup.id" :key="researchGroup.id">{{ researchGroup.name }}</option>
            </select>
        </div>
        <div class="col-6">
            <label class="col-form-label-lg">
                    Search for Research Group Page By
            </label>
            <b-form-select v-model="selectedProjectDirector" :options="projectDirectorsOptions"></b-form-select>
        </div>
    </div>
</template>

<script>
    export default {
        name: "FundingCyclesList",
        props: {
            fundingCycles: {
                type: Array,
            }
        },
        data() {
            return {
                selectedProjectDirector: null,
                researchGroups: [],
                researchGroupDisabled: true,
                projectDirectorsOptions: [{ value: null, text: '[Please select an associated Project Director]' }],
                projectDirectorIds: []
            }
        },
        methods: {
            populateResearchGroups: function(event) {
                this.researchGroups = [];
                this.researchGroupDisabled = true;
                this.fundingCycles.forEach(fundingCycle => {
                    if (fundingCycle.id === Number(event.target.value)) {
                        this.researchGroups = fundingCycle.researchGroups;
                        this.researchGroupDisabled = false;
                    }
                })
            },
            selectResearchGroup: function(event) {
                if (event.target.value) {
                    this.openResearchGroupLandingPage(event.target.value);
                }
            },
            populateProjectDirectors: function () {
                this.fundingCycles.forEach(fundingCycle => {
                    fundingCycle.researchGroups.forEach(researchGroup => {
                        researchGroup.projectDirectors.forEach(projectDirector => {
                            if (this.projectDirectorIds.indexOf(projectDirector.id) === -1) {
                                this.projectDirectorIds.push(projectDirector.id);
                                this.makeProjectDirectorOption(researchGroup.id, projectDirector.name);
                            } else {
                                this.projectDirectorIds.push(projectDirector.id);
                                this.makeProjectDirectorOption(
                                    researchGroup.id,
                                    projectDirector.name + ' - ' + researchGroup.shortName
                                );
                            }
                        })
                    })
                })
            },
            openResearchGroupLandingPage: function (researchGroupId) {
                window.open("/research-group/about/" + researchGroupId, '_blank');
            },
            makeProjectDirectorOption: function (id, name) {
                this.projectDirectorsOptions.push({
                    value: id,
                    text: name
                })
            }
        },
        created() {
            this.populateProjectDirectors();
        },
        watch: {
            selectedProjectDirector: function () {
               this.openResearchGroupLandingPage(this.selectedProjectDirector);
            }
        }
    };
</script>

<style scoped>
    .border-right {
        border-right: 1px solid black;
    }
</style>
