import Vue from "vue";
import PersonProfile from "@/vue/PersonProfile";

new Vue({
    components: { PersonProfile },
    data() {
        return {
            person: {}
        }
    },
    beforeMount() {
        this.person = Number(this.$el.attributes['data-name'].value);
    },
    template: `<PersonProfile :person='person'/>`,
}).$mount("#person-profile");
