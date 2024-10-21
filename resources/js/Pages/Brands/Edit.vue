<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";
import { ref } from 'vue';

const props = defineProps({
    brand: Object
})


const saveBrand = () => {
    router.put(`/brands/${props.brand.id}`, props.brand,
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
    { label: 'Brands', url: '/brands' },
    { label: 'Edit Brand' },
]);

</script>
<template>

    <Head :title="'Edit Brand #' + brand.id" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit Brand #' + brand.id" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton @click="saveBrand()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon> Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="row mb-3">
                <div class="col-6">
                    <label for="brand-name" class="form-label">Name:</label>
                    <input id="brand-name" class="form-control" v-model="brand.name">
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
