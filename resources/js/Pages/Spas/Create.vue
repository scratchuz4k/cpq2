<script setup>
import { reactive, ref } from "vue";
import Button from 'primevue/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";

const props = defineProps({
    suppliers: Object,
    users: Object,
})

const spa = reactive({
    data: {
        operation: 'Add',
        value: 0
    },
    supplier_id: null,
    user_id: null,
    name: null
})

const operations = [
    'Add',
    'Subtract',
    'Multiply',
    'Divide'
]
const saveSpa = () => {
    router.post(`/spas`, spa,
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
    { label: 'My Account', url: '/myaccount' },
    { label: 'Spas', url: '/spas' },
]);

</script>
<template>
    <Head title="Spas" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Spa" :breadcrumbs="items">
                <template #buttons>
                    <Button @click="saveSpa()" size="small">
                        <font-awesome-icon :icon="['fas', 'save']"></font-awesome-icon> Save
                    </Button>
                </template>
            </Header>
            <div class="col-6">
                <div class="row mb-3">
                    <label for="spa-name" class="form-label">Name:</label>
                    <input id="spa-name" class="form-control" v-model="spa.name">
                </div>
                <div class="row mb-3">
                    <label class="form-label">User:</label>
                    <select class="form-select" v-model="spa.user_id">
                        <option v-for="user in users" :value="user.id">{{ user.name }}</option>
                    </select>
                </div>
                <div class="row mb-3">
                    <label class="form-label">Supplier:</label>
                    <select class="form-select" v-model="spa.supplier_id">
                        <option v-for="supplier in suppliers" :value="supplier.id">{{ supplier.name }}</option>
                    </select>
                </div>
                <div class="row mb-3">
                    <label class="form-label">Pricing Rules:</label>
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
    </AuthenticatedLayout>
</template>
