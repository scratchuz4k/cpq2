<script setup>
import { reactive, ref } from "vue";
import Button from 'primevue/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";

const props = defineProps({
    plainTextToken: String,
    users: Array,
    token: Object,
})

const items = ref([
    { label: 'My Account', url: '/myaccount' },
    { label: 'Tokens', url: '/tokens' },
]);

</script>
<template>

    <Head title="Tokens" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Token" :breadcrumbs="items"> </Header>
            <div class="col-6">
                <div class="mb-3 alert alert-danger" v-if="plainTextToken">
                    <label for="token-name" class="form-label">Save this token for api usage:</label>
                    <p class="m-0"> {{ plainTextToken }}</p>
                </div>
                <div class="mb-3">
                    <label for="token-name" class="form-label">Name:</label>
                    <input id="token-name" class="form-control" v-model="token.name" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">User:</label>
                    <select class="form-select" v-model="token.tokenable_id" disabled>
                        <option v-for="user in users" :value="user.id">
                            {{ user.name }}
                            ({{ user.roles.map(x => x.name).join(',') }})
                        </option>
                    </select>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
