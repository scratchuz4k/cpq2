<script setup>
import Button from 'primevue/button';
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
    { label: 'My', url: '/myaccount' },
    { label: 'Brands', url: '/brands' },
]);

</script>
<template>

    <Head title="Brands" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit Brand #' + brand.id" :breadcrumbs="items">
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
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
