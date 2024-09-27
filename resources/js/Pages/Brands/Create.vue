<script setup>
import { ref } from "vue";
import Breadcrumb from 'primevue/breadcrumb';
import Button from 'primevue/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from "@inertiajs/vue3";

const brand = useForm({
    name: null,
})

const saveBrand = () => {
    brand.post("/brands", brand)
}
const items = ref([
    { label: 'My', url: '/myaccount' },
    { label: 'Brands', url: '/brands' },
]);

</script>
<template>

    <Head title="Brands" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Brand" :breadcrumbs="items">
                <template #buttons>
                    <Button @click="saveBrand()" size="small">
                        <font-awesome-icon :icon="['fas', 'save']"></font-awesome-icon> Save
                    </Button>
                </template>
            </Header>
            <div class="row mb-3">
                <div class="col">
                    <label for="brand-name" class="form-label">Name:</label>
                    <input id="brand-name" class="form-control" v-model="brand.name">
                    <div class="text-danger" v-if="brand.errors.name">{{ brand.errors.name }}</div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
