<script setup>
import { reactive, ref } from "vue";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";

const props = defineProps({
    spa: Object,
    users: Object,
    suppliers: Object
})

if(!props.spa.data) {
    props.spa.data = {};
}

const operations = [
    'Add',
    'Subtract',
    'Multiply',
    'Divide'
]

const saveSpa = () => {
    router.put(`/spas/${props.spa.id}`, props.spa,
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
    { label: 'SPAs', url: '/spas' },
    { label: 'Edit SPA' },
]);

</script>
<template>

    <Head :title="'Edit SPA #' + spa.id" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit SPA #' + spa.id" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton @click="saveSpa()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon> Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="row mb-3">
                <div class="col-6">
                    <div class="row mb-3">
                        <label for="spa-name" class="form-label">Name:</label>
                        <input id="spa-name" class="form-control" v-model="spa.name">
                    </div>
                    <div class="row mb-3">
                        <label for="spa-name" class="form-label">User:</label>
                        <select class="form-select" v-model="spa.user_id">
                            <option v-for="user in users" :value="user.id">{{ user.name }}</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <label for="supplier-name" class="form-label">Supplier:</label>
                        <select class="form-select" v-model="spa.supplier_id">
                            <option v-for="supplier in suppliers" :value="supplier.id">{{ supplier.name }}</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <label for="supplier-name" class="form-label">Pricing Rules:</label>
                        <div class="d-flex p-0">
                            <select class="form-select w-50" v-model="spa.data.operation">
                                <option v-for="operation in operations" :value="operation">{{ operation}}</option>
                            </select>
                            <input id="spa-value" class="form-control w-50" v-model="spa.data.value">
                        </div>
                        <div class="form-text px-0">
                            Example: to increase prices by 20%, select the "MULTIPLY" operation and enter "1.2" as the value.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
