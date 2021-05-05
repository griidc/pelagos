<template>
    <div v-show="showProfile" class="container">
        <div class="main-body">
            <div class="row gutters-sm">
                <div class="col-md-4 mb-3">
                    <AvatarCard :person-details="personProfileData"/>
                </div>
                <div class="col-md-8">
                    <UserDetailsCard :person-details="personProfileData"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { getApi } from "@/vue/utils/axios";
import AvatarCard from "@/vue/components/person-profile/AvatarCard";
import UserDetailsCard from "@/vue/components/person-profile/UserDetailsCard";

export default {
    name: "PersonProfile",
    components: { AvatarCard, UserDetailsCard },
    props: {
        personId: {
            type: Number
        }
    },
    data() {
        return {
            showProfile: false,
            personProfileData: {},
            avatarInfo: {}
        }
    },
    mounted() {
        getApi(Routing.generate('pelagos_api_people_get') + "/" + this.personId, this, true)
            .then(response => {
                this.personProfileData = response;
                this.showProfile = true;
                console.log(response);
            })
            .catch(error => {
                console.log(error);
                this.showProfile = false;
            })
    }
}
</script>

<style scoped lang="scss">
.main-body {
    padding: 15px;
}
.card {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, .1), 0 1px 2px 0 rgba(0, 0, 0, .06);
}

.gutters-sm {
    margin-right: -8px;
    margin-left: -8px;
    >.col {
        padding-right: 8px;
        padding-left: 8px;
    }
    >[class*=col-] {
        padding-right: 8px;
        padding-left: 8px;
    }
}
.mb-3 {
    margin-bottom: 1rem !important;
}
.my-3 {
    margin-bottom: 1rem !important;
}
.h-100 {
    height: 100% !important;
}

</style>
