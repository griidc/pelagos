<template>
    <SearchForm :template="template"/>
</template>

<script>
    import SearchForm from "./components/search/SearchForm";
    export default {
        name: "SearchApp",
        components: { SearchForm },
        props: {
            template: {
                type: String
            }
        }
    };
</script>

<style scoped>

</style>
