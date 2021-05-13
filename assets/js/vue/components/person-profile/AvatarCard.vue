<template>
    <b-card>
        <b-card-body>
            <div class="d-flex flex-column align-items-center text-center">
                <img src="~images/user-profile-pic.png" alt="User Profile Picture"
                     class="rounded-circle" width="150">
                <div class="mt-3">
                    <h4>{{ `${personDetails.firstName}  ${personDetails.lastName}` }}</h4>
                    <p class="text-secondary mb-1">{{ personDetails.position }}</p>
                    <p class="text-muted font-size-sm">{{ personDetails.organization }}</p>
                </div>
            </div>
        </b-card-body>
    </b-card>
</template>

<script>
export default {
    name: "AvatarCard",
    props: {
        personDetails: {
            type: Object
        }
    }
}
</script>

<style scoped>

</style>
