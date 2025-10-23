<script setup>
import Select from 'primevue/select';
import ToggleSwitch from 'primevue/toggleswitch';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    suppliers: Array
});

const auth_types = [
    { value: 'basic', name: 'Basic' },
    { value: 'basic_client', name: 'Basic Client' },
    { value: 'token', name: 'Token' }
];
const api = useForm({
    code: null,
    name: null,
    auth_type: 'basic',
    username: null,
    password: null,
    client_id: null,
    token: null,
    is_active: true
});

const clearAuth = () => {
    api.username = null;
    api.password = null;
    api.client_id = null;
    api.token = null;
};

const saveApi = () => {
    router.post('/apis', api, {
        onSuccess: (page) => {
            notyf.success('Created with Success');
        },
        onError: (errors) => {
            let string = '';
            Object.keys(errors).forEach((key) => {
                string += errors[key] + '<br>';
            });
            notyf.error(string);
        }
    });
};

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Apis', url: '/apis' },
    { label: 'New Api' }
]);
</script>
<template>
    <Head title="New Api" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Api" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-api" @click="saveApi()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="row">
                <div class="col-6">
                    <div class="mb-3 d-flex flex-column">
                        <label class="form-label" for="active-filter"> Active: </label>
                        <ToggleSwitch id="active-filter" v-model="api.is_active">
                            <template #handle>
                                <font-awesome-icon
                                    :icon="api.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']"
                                    class="text-primary"
                                />
                            </template>
                        </ToggleSwitch>
                    </div>
                    <div class="mb-3">
                        <label for="api-code" class="form-label">Code:</label>
                        <Select
                            :inputId="'api-code'"
                            v-model="api.code"
                            editable
                            :options="props.suppliers"
                            optionLabel="name"
                            optionValue="value"
                            placeholder="Enter a Code"
                            class="w-100"
                        />
                    </div>
                    <div class="mb-4">
                        <label for="api-name" class="form-label">Name:</label>
                        <input id="api-name" class="form-control" v-model="api.name" />
                    </div>
                    <h4>Authentication</h4>
                    <hr class="login-seperator" />
                    <div class="mb-3">
                        <label for="api-code" class="form-label">Auth Type:</label>
                        <Select
                            :inputId="'select-apis-code'"
                            v-model="api.auth_type"
                            :options="auth_types"
                            optionLabel="name"
                            optionValue="value"
                            placeholder="Select an Auth"
                            class="w-100"
                            @change="clearAuth()"
                        />
                    </div>
                    <div v-if="api.auth_type == 'basic_client'" class="mb-3">
                        <label for="api-client_id" class="form-label">Client ID:</label>
                        <input id="api-client_id" class="form-control" v-model="api.client_id" />
                    </div>
                    <template v-if="api.auth_type == 'basic' || api.auth_type == 'basic_client'">
                        <div class="mb-3">
                            <label for="api-username" class="form-label">Username:</label>
                            <input id="api-username" class="form-control" v-model="api.username" />
                        </div>
                        <div class="mb-3">
                            <label for="api-code" class="form-label">Password:</label>
                            <input
                                id="api-password"
                                class="form-control"
                                type="password"
                                autocomplete="off"
                                v-model="api.password"
                            />
                        </div>
                    </template>
                    <template v-if="api.auth_type == 'token'">
                        <div class="mb-3">
                            <label for="api-token" class="form-label">Token:</label>
                            <input id="api-token" autocomplete="off" class="form-control" v-model="api.token" />
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
