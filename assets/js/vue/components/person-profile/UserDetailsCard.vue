<template>
    <b-card class="mb-3">
        <b-card-body>
            <div class="row">
                <div class="col-sm-3">
                    <h6 class="mb-0">Full Name</h6>
                </div>
                <div class="col-sm-9 text-secondary">
                    {{ `${personDetails.firstName}  ${personDetails.lastName}` }}
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-sm-3">
                    <h6 class="mb-0">Email</h6>
                </div>
                <div class="col-sm-9 text-secondary">
                    {{ personDetails.emailAddress }}
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-sm-3">
                    <h6 class="mb-0">Phone</h6>
                </div>
                <div class="col-sm-9 text-secondary">
                    {{ personDetails.phoneNumber }}
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-sm-3">
                    <h6 class="mb-0">Address</h6>
                </div>
                <div class="col-sm-9 text-secondary">
                    {{ `${personDetails.organization},
                        ${personDetails.city},
                        ${personDetails.administrativeArea} - ${personDetails.postalCode},
                        ${personDetails.country}` }}
                </div>
            </div>
        </b-card-body>
    </b-card>
</template>

<script>
export default {
    name: "UserDetailsCard",
    props: {
        personDetails: {
            type: Object
        }
    }
}
</script>

<style scoped>

</style>
