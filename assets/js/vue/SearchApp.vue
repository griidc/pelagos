<template>
    <div class="bootstrap">
        <section class="section-content bg pt-5">
            <div class="container">
                <div class="card card-header">
                    <SearchForm/>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import SearchForm from "./components/search/SearchForm";
    export default {
        name: "SearchApp",
        components: { SearchForm }
    };
</script>

<style scoped>

</style>
