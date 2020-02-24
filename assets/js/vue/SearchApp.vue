<template>
    <div class="bootstrap">
        <SearchForm/>
        <section class="section-content pt-3 bg">
            <div class="container">
                <article class="card card-product">
                    <div class="card-body text-center">
                        <h5 class="card-title">
                            GRIIDC Data Sets
                        </h5>
                        <p class="card-text">
                            Choose from thousands of scientific datasets from various fields
                            including oceanography, biology, ecology, chemistry, social science,
                            and others. Datasets are primarily focused on the Deepwater Horizon
                            oil spill in the Gulf of Mexico; however, some datasets are related
                            to other topics and locations around the world.
                        </p>
                    </div>
                </article>
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
