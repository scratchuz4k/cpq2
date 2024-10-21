<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";
import { ref } from 'vue';

const props = defineProps({
    region: Object,
    merchants: Object,
    users: Object
})


const saveRegion= () => {
    router.put(`/regions/${props.region.id}`, props.region,
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
    { label: 'Regions', url: '/regions' },
    { label: 'Edit Region' },
]);

</script>
<template>

    <Head :title="'Edit Region #' + region.id" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit Region #' + region.id" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton @click="saveRegion()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon> Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="col-6">
                <div class="row mb-3">
                    <label for="merchant-name" class="form-label">Merchant:</label>
                    <select class="form-select" v-model="region.merchant_id">
                        <option v-for="merchant in merchants" :value="merchant.id">{{ merchant.name }}</option>
                    </select>
                </div>
                <div class="row mb-3">
                    <label for="user-name" class="form-label">User:</label>
                    <select class="form-select" v-model="region.user_id">
                        <option v-for="user in users" :value="user.id">{{ user.name }}</option>
                    </select>
                </div>
                <div class="row mb-3">
                    <label for="region-name" class="form-label">Name:</label>
                    <input id="region-name" class="form-control" v-model="region.name">
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
