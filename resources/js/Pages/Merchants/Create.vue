<script setup>
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import ToggleSwitch from 'primevue/toggleswitch';

const merchant = useForm({
    name: null,
    is_active: true
});

const saveMerchant = () => {
    router.post('/merchants', merchant,
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
        });
};
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Merchants', url: '/merchants' },
    { label: 'New Merchant' }
]);

</script>
<template>
    <Head title="new Merchant" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Merchant" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-merchant" @click="saveMerchant()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="row mb-3">
                <div class="mb-3 d-flex flex-column">
                    <label class="form-label" for="active-filter">
                        Active:
                    </label>
                    <ToggleSwitch id="active-filter" v-model="merchant.is_active">
                        <template #handle>
                            <font-awesome-icon
                                :icon="merchant.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']"
                                class="text-primary"
                            />
                        </template>
                    </ToggleSwitch>

                </div>
                <div class="col-6">
                    <label for="merchant-name" class="form-label">Name:</label>
                    <input id="merchant-name" class="form-control" v-model="merchant.name">
                    <div class="text-danger" v-if="merchant.errors.name">{{ merchant.errors.name }}</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
