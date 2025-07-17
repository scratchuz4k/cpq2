<script setup>
import { reactive, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Select from 'primevue/select';
import ToggleSwitch from 'primevue/toggleswitch';

const props = defineProps({
    users: Object
});

const token = reactive({
    user_id: null,
    name: null,
    is_active: true
});

const saveToken = () => {
    router.post(`/tokens`, token, {
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
    { label: 'Tokens', url: '/tokens' },
    { label: 'New Token' }
]);
</script>
<template>
    <Head title="New Token" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Token" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton @click="saveToken()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="col-6">
                <div class="mb-3 d-flex flex-column">
                    <label class="form-label" for="active-filter"> Active: </label>
                    <ToggleSwitch id="active-filter" v-model="token.is_active">
                        <template #handle>
                            <font-awesome-icon
                                :icon="token.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']"
                                class="text-primary"
                            />
                        </template>
                    </ToggleSwitch>
                </div>
                <div class="mb-3">
                    <label for="token-name" class="form-label">Name:</label>
                    <input id="token-name" class="form-control" v-model="token.name" />
                </div>
                <div class="mb-3">
                    <label for="token-user" class="form-label">User:</label>

                    <Select
                        v-model="token.user_id"
                        :inputId="'token-user'"
                        :options="users"
                        class="w-100"
                        optionLabel="description"
                        optionValue="id"
                        placeholder="Select a User"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
