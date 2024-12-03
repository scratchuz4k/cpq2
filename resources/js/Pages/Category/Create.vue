<script setup>
import { reactive, ref } from "vue";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";

const props = defineProps({
})

const category = reactive({
    name: null,
    code: null,
})

const saveCate = () => {
    router.post(`/categories`, category,
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
        }
    )
}
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Categories', url: '/categories' },
    { label: 'New Category' },
]);

</script>
<template>
    <Head title="New Product" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Product" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-product" @click="saveCate()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon> Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="col-6">
                <div class="mb-3">
                    <label for="product-code" class="form-label">Code:</label>
                    <input id="product-code" class="form-control" v-model="category.code">
                </div>
                <div class="mb-3">
                    <label for="product-name" class="form-label">Name:</label>
                    <input id="product-name" class="form-control" v-model="category.name">
                </div>

            </div>

        </div>
    </AuthenticatedLayout>
</template>
