<template>
    <div class="form-group">
        <label class="col-form-label col-form-label-lg">
            Funding Cycles
        </label>

        <select @change="populateResearchGroups" class="form-control">
            <option value="" selected>[Please select a Funding Cycle]</option>
            <option v-for="fundingCycle in fundingCycles" :value="fundingCycle.id" :key="fundingCycle.id">{{ fundingCycle.name }}</option>
        </select>

        <label class="col-form-label col-form-label-lg">
            Research Groups
        </label>

        <select @change="selectResearchGroup" class="form-control" :disabled="researchGroupDisabled">
            <option v-for="researchGroup in researchGroups" :value="researchGroup.id" :key="researchGroup.id">{{ researchGroup.name }}</option>
        </select>

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
                researchGroups: [],
                researchGroupDisabled: true,
            }
        },
        methods: {
            populateResearchGroups: function(event) {
                this.researchGroups = [];
                this.researchGroupDisabled = true;
                this.fundingCycles.forEach(fundingCycle => {
                    if (fundingCycle.id == event.target.value) {
                        this.researchGroups = fundingCycle.researchGroups;
                        this.researchGroupDisabled = false;
                    }
                })
            },
            selectResearchGroup: function(event) {
                if (event.target.value != "") {
                    window.open("/research-group/about/" + event.target.value, '_blank');
                }
            }
        }
    };
</script>

<style scoped>

</style>
