<script setup>
import { reactive, ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import InputSelect from '@/Components/InputSelect.vue';

const props = defineProps({
    merchants: Object,
    users: Object
});

const form = reactive({
    merchant: null,
    user: null,
    name: null
});

const saveRegion = () => {
    router.post(
        '/regions',
        {
            ...form,
            merchant_id: form.merchant.id,
            user_id: form.user.id
        },
        {
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
        }
    );
};
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Regions', url: '/regions' },
    { label: 'New Region' }
]);
</script>
<template>
    <Head title="New Region" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Region" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton @click="saveRegion()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon> Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="col-6">
                <div class="row mb-3">
                    <label for="merchant-name" class="form-label">Merchant:</label>
                    <InputSelect
                        id="region-merchant"
                        url="/merchants"
                        :fields="['name']"
                        v-model="form.merchant"
                        :is-multiple="false"
                        placeholder="Select an Merchant"
                    />
                </div>
                <div class="row mb-3">
                    <label for="user-name" class="form-label">User:</label>
                    <InputSelect
                        id="region-user"
                        url="/users"
                        :fields="['name']"
                        v-model="form.user"
                        :is-multiple="false"
                        placeholder="Select an User"
                    />
                </div>
                <div class="row mb-3">
                    <label for="region-name" class="form-label">Name:</label>
                    <div>
                        <input id="region-name" class="form-control" v-model="form.name" required />
                    </div>
                    <div class="text-danger" v-if="form.errors?.name">{{ form.errors?.name }}</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
