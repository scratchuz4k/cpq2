<script setup>
import { ref } from "vue";
import Breadcrumb from 'primevue/breadcrumb';
import Button from 'primevue/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from "@inertiajs/vue3";

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
    region.post("/regions", region)
}
const items = ref([
    { label: 'My Account', url: '/myaccount' },
    { label: 'Regions', url: '/regions' },
]);

</script>
<template>
 {{ merchants}}
    <Head title="Regions" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Region" :breadcrumbs="items">
                <template #buttons>
                    <Button @click="saveRegion()" size="small">
                        <font-awesome-icon :icon="['fas', 'save']"></font-awesome-icon> Save
                    </Button>
                </template>
            </Header>
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
                <div class="col-6">
                    <label for="region-name" class="form-label">Name:</label>
                    <input id="region-name" class="form-control" v-model="region.name" required>
                    <div class="text-danger" v-if="region.errors.name">{{ region.errors.name }}</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
