<script setup>
import { reactive, ref } from "vue";
import Button from 'primevue/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";

const props = defineProps({
    spa: Object,
    users: Object,
    suppliers: Object
})

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
            <div class="row mb-3">
                <div class="col-6">
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
