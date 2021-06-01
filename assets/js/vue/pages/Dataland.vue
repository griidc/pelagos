<template>
    <div class="container">
        <div class="row">
            <main class="col-lg-9 overflow-auto">
                <div class="row">
                    <h2>Abstract</h2>
                </div>
            </main>
            <aside class="col-lg-3">
                <b-card-group>
                    <b-card title="Card title" sub-title="Card subtitle">
                        <b-card-text>
                            Some quick example text to build on the <em>card title</em> and make up the bulk of the
                            card's
                            content.
                        </b-card-text>

                        <b-card-text>A second paragraph of text in the card.</b-card-text>

                        <a href="#" class="card-link">Card link</a>
                        <b-link href="#" class="card-link">Another link</b-link>
                    </b-card>
                    <b-card title="Card title" sub-title="Card subtitle">
                        <b-card-text>
                            Some quick example text to build on the <em>card title</em> and make up the bulk of the
                            card's
                            content.
                        </b-card-text>

                        <b-card-text>A second paragraph of text in the card.</b-card-text>

                        <a href="#" class="card-link">Card link</a>
                        <b-link href="#" class="card-link">Another link</b-link>
                    </b-card>
                    <b-card title="Card title" sub-title="Card subtitle">
                        <b-card-text>
                            Some quick example text to build on the <em>card title</em> and make up the bulk of the
                            card's
                            content.
                        </b-card-text>

                        <b-card-text>A second paragraph of text in the card.</b-card-text>

                        <a href="#" class="card-link">Card link</a>
                        <b-link href="#" class="card-link">Another link</b-link>
                    </b-card>
                    <b-card title="Card title" sub-title="Card subtitle">
                        <b-card-text>
                            Some quick example text to build on the <em>card title</em> and make up the bulk of the
                            card's
                            content.
                        </b-card-text>

                        <b-card-text>A second paragraph of text in the card.</b-card-text>

                        <a href="#" class="card-link">Card link</a>
                        <b-link href="#" class="card-link">Another link</b-link>
                    </b-card>
                </b-card-group>
            </aside>
        </div>
    </div>
</template>

<script>
import {getApi} from "@/vue/utils/axiosService";

export default {
    name: "Dataland",
    props: {
        udi: {
            type: String
        }
    },
    data() {
        return {
            details: {}
        }
    },
    created() {
        getApi(
            `${Routing.generate('pelagos_api_datasets_get')}?udi=${this.udi}`,
            {thisComponent: this, addLoading: true}
        ).then(response => {
            console.log(response.data);
            this.details = response.data;
        })
    }
}
</script>

<style scoped>

</style>