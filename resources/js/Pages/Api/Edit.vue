<script setup>
import Select from 'primevue/select';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ToggleSwitch from 'primevue/toggleswitch';

const props = defineProps({
    api: Object,
    suppliers: Array
});

const auth_types = [
    { value: 'basic', name: 'Basic' },
    { value: 'basic_client', name: 'Basic Client' },
    { value: 'token', name: 'Token' },
    { value: 'vmo2_client', name: 'VMO2 Client' }
];
const data = useForm(props.api);

const clearAuth = () => {
    data.username = null;
    data.password = null;
    data.client_id = null;
    data.token = null;
};

const saveApi = () => {
    router.put(`/apis/${data.id}`, data, {
        onSuccess: (page) => {
            notyf.success('Updated with Success');
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
    { label: 'Edit Api' }
]);
</script>
<template>
    <Head :title="'Edit Item #' + data.id" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit Item #' + data.id" :breadcrumbs="items">
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
                        <ToggleSwitch id="active-filter" v-model="data.is_active">
                            <template #handle>
                                <font-awesome-icon
                                    :icon="data.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']"
                                    class="text-primary"
                                />
                            </template>
                        </ToggleSwitch>
                    </div>
                    <div class="mb-3">
                        <label for="api-code" class="form-label">Code:</label>
                        <Select
                            :inputId="'api-code'"
                            v-model="data.code"
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
                        <input id="api-name" class="form-control" v-model="data.name" />
                    </div>
                    <h4>Authentication</h4>
                    <hr class="login-seperator" />
                    <div class="mb-3">
                        <label for="api-code" class="form-label">Auth Type:</label>
                        <Select
                            :inputId="'select-apis-code'"
                            v-model="data.auth_type"
                            :options="auth_types"
                            optionLabel="name"
                            optionValue="value"
                            placeholder="Select an Auth"
                            class="w-100"
                            @change="clearAuth()"
                        />
                    </div>
                    <div v-if="data.auth_type == 'basic_client'" class="mb-3">
                        <label for="api-client_id" class="form-label">Client ID:</label>
                        <input id="api-client_id" class="form-control" v-model="data.client_id" />
                    </div>
                    <template v-if="data.auth_type == 'basic' || data.auth_type == 'basic_client'">
                        <div class="mb-3">
                            <label for="api-username" class="form-label">Username:</label>
                            <input id="api-username" class="form-control" v-model="data.username" />
                        </div>
                        <div class="mb-3">
                            <label for="api-code" class="form-label">Password:</label>
                            <input
                                id="api-password"
                                class="form-control"
                                type="password"
                                autocomplete="off"
                                v-model="data.password"
                            />
                        </div>
                    </template>
                    <template v-if="data.auth_type == 'token'">
                        <div class="mb-3">
                            <label for="api-token" class="form-label">Token:</label>
                            <input id="api-token" autocomplete="off" class="form-control" v-model="data.token" />
                        </div>
                    </template>
                    <template v-if="data.auth_type == 'vmo2_client'">
                        <div class="mb-3">
                            <label for="api-app_id" class="form-label">App ID:</label>
                            <input id="api-app_id" autocomplete="off" class="form-control" v-model="data.app_id" />
                        </div>
                        <div class="mb-3">
                            <label for="api-token" class="form-label">Client Secret:</label>
                            <input id="api-token" autocomplete="off" class="form-control" v-model="data.token" />
                        </div>
                        <div class="mb-3">
                            <label for="api-username" class="form-label">User ID:</label>
                            <input id="api-username" autocomplete="off" class="form-control" v-model="data.username" />
                        </div>
                        <div class="mb-3">
                            <label for="api-client_id" class="form-label">Customer ID:</label>
                            <input
                                id="api-client_id"
                                autocomplete="off"
                                class="form-control"
                                v-model="data.client_id"
                            />
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
