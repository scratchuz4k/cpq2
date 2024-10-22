<script setup>
import { ref } from "vue";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, router, useForm} from "@inertiajs/vue3";

const props = defineProps({
    merchants: Object,
    users: Object
})

const region = useForm({
    merchant_id: null,
    user_id: null,
    name: null,
})

const saveRegion = () => {
    router.post("/regions", region,
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
    { label: 'Regions', url: '/regions' },
    { label: 'New Region' },
]);

</script>
<template>
 {{ merchants}}
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
                    <input id="region-name" class="form-control" v-model="region.name" required>
                    <div class="text-danger" v-if="region.errors.name">{{ region.errors.name }}</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
