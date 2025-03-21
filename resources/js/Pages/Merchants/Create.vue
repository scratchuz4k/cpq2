<script setup>
import { ref } from "vue";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, router, useForm} from "@inertiajs/vue3";

const merchant = useForm({
    name: null,
})

const saveMerchant = () => {
    router.post("/merchants", merchant,
        {
            onSuccess: (page) => {
                notyf.success("Created with Success")
            },
            onError: (errors) => {
                let string = ''
                Object.keys(errors).forEach((key) => {
                    string += errors[key] + '<br>'
                })
                notyf.error(string)
            },
        })
}
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Merchants', url: '/merchants' },
    { label: 'New Merchant' },
]);

</script>
<template>
    <Head title="new Merchant" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Merchant" :breadcrumbs="items">
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
                    <div class="text-danger" v-if="merchant.errors.name">{{ merchant.errors.name }}</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
