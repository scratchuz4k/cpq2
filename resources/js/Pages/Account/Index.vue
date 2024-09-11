<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
defineProps({
    tenants: Array
})

</script>

<template>
    <AuthenticatedLayout>
        <div class="panel">
            <div class="containter">
                <h1>My Account</h1>
                <div class="row">
                    <div class="col">

                        <div class="mb-3">
                            <p>Your email: {{ $page.props.auth.user.name }}</p>
                            <hr class="dropdown-divider">

                            <Link :href="route('logout')" class="btn btn-danger" method="post" as="button">
                            Logout
                            </Link>
                        </div>

                        <h2 v-if="true">Company Accounts</h2>
                        <div v-for="tenant in tenants"> {{ tenant }}</div>
                        {{ tenants }}

                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>