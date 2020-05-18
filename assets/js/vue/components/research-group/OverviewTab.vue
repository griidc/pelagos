<template>
    <div>
        <h4>{{ info.name }}</h4>
        <hr>
        <h6 style="font-weight: bold">Description</h6>
        <p>{{ info.description }}</p>
        <h6 style="font-weight: bold">Funding Organization</h6>
        <p>{{ info.fundingCycle.fundingOrganization.name }}</p>
        <h6 style="font-weight: bold">Funding Cycle</h6>
        <p>{{ info.fundingCycle.name }}</p>
    </div>
</template>

<script>
    export default {
        name: "InfoTab",
        props: {
            info: {
                type: Object
            }
        },
    }
</script>

<style scoped>

</style>
