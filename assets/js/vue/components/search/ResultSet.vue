<template>
    <div>
        <section class="section-content bg pt-5">
            <div class="container">
                <div class="row d-flex flex-row justify-content-center">
                    <h3 id="count">
                        Found {{ results.count }} results for: "{{ results.formValues.query }}"
                    </h3>
                </div>
            </div>
        </section>
        <section class="section-content bg padding-y">
            <div class="container">
                <div class="row">
                    <FacetGroups :facets="results.facetInfo"/>
<!--                    <main class="col-sm-9 overflow-auto">-->
<!--                        {% for result in results %}-->
<!--                        {% include 'Search/dataset-row.html.twig'  %}-->
<!--                        {% endfor %}-->
<!--                    </main>-->
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import FacetGroups from "./FacetGroups";
    export default {
        name: "ResultSet",
        components: { FacetGroups },
        props: {
            results: {
                type: Object
            }
        }
    }
</script>

<style scoped>

</style>