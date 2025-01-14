<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";
import { ref } from 'vue';

const props = defineProps({
    merchant: Object
})


const saveMerchant = () => {
    router.put(`/merchants/${props.merchant.id}`, props.merchant,
        {
            onSuccess: (page) => {
                notyf.success("Updated with Success")
            },
            onError: (errors) => {
                let string = ''
                Object.keys(errors).forEach((key) => {
                    string += errors[key] + '<br>'
                })
                notyf.error(string)
            },
        }
    )
}

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Merchants', url: '/merchants' },
    { label: 'Edit Merchant' },
]);

</script>
<template>

    <Head :title="'Edit Merchant #' + merchant.id" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit Merchant #' + merchant.id" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-merchant" @click="saveMerchant()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon> Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="row mb-3">
                <div class="col-6">
                    <label for="merchant-name" class="form-label">Name:</label>
                    <input id="merchant-name" class="form-control" v-model="merchant.name">
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
