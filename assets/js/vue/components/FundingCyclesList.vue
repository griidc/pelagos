<template>
    <div>
        <select @change="doSomething" class="form-control">
            <option value="" selected>[Please select a Funding Cycle]</option>
            <option v-for="fundingCycle in fundingCycles" :value="fundingCycle.id" :key="fundingCycle.id">{{ fundingCycle.name }}</option>
        </select>
        
        <select @change="doSomething" class="form-control">
            <option value="" selected>[Please select a Funding Cycle]</option>
            <option v-for="researchGroup in researchGroups" :value="researchGroup.id" :key="researchGroup.id">{{ researchGroup.name }}</option>
        </select>
        
    </div>
</template>

<script>
    export default {
        name: "FundingCyclesList",
        props: {
            fundingCycles: {
                type: Object,
            }
        },
        data() {
            return {
                researchGroups: []
            }
        },
        methods: {
            doSomething: function() {
                alert('click');
            }
        }
    };
</script>

<style scoped>

</style>
