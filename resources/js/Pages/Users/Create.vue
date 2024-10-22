<script setup>
import {computed, reactive, ref} from "vue";
import Button from 'primevue/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, router, useForm} from "@inertiajs/vue3";

const props = defineProps({
    roles: Object,
    managers: Object,
    parent_id: Number
})

const user = useForm({
    name: null,
    email: null,
    password: null,
    role_id: null,
    parent_id: props.parent_id ?? null,
    commission_rate: null
})

const showCommissionRate = computed(() => user.role_id === 4)

const saveUser = () => {
    router.post(`/users`, user,
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
    { label: 'Users', url: '/users' },
]);

</script>
<template>
    <Head title="Users" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New User" :breadcrumbs="items">
                <template #buttons>
                    <Button @click="saveUser()" size="small">
                        <font-awesome-icon :icon="['fas', 'save']"></font-awesome-icon> Save
                    </Button>
                </template>
            </Header>
            <div class="col-6">
                <div class="row mb-3">
                    <label for="user-name" class="form-label">Name:</label>
                    <input id="user-name" class="form-control" v-model="user.name" required>
                    <div class="text-danger" v-if="user.errors.name">{{ user.errors.name }}</div>
                </div>
                <div class="row mb-3">
                    <label for="user-email" class="form-label">Email:</label>
                    <input id="user-email" type="email" class="form-control" v-model="user.email" required>
                    <div class="text-danger" v-if="user.errors.email">{{ user.errors.email }}</div>
                </div>
                <div class="row mb-3">
                    <label for="user-password" class="form-label">Password:</label>
                    <input id="user-password" type="password" class="form-control" v-model="user.password" required>
                    <div class="text-danger" v-if="user.errors.password">{{ user.errors.password }}</div>
                </div>
                <div class="row mb-3">
                    <label for="supplier-name" class="form-label">Role:</label>
                    <select class="form-select" v-model="user.role_id">
                        <option v-for="role in roles" :value="role.id">{{ role.name }}</option>
                    </select>
                </div>
                <div v-if="showCommissionRate" class="row mb-3">
                    <label for="user-commission-rate" class="form-label">Commission Rate:</label>
                    <div class="input-group p-0">
                        <input id="user-commission-rate" type="number" class="form-control" v-model="user.commission_rate">
                        <div class="text-danger" v-if="user.errors.commission_rate">{{ user.errors.commission_rate }}</div>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="user-name" class="form-label">Manager:</label>
                    <select class="form-select" v-model="user.parent_id">
                        <option v-for="manager in managers" :value="manager.id">{{ manager.name }}</option>
                    </select>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
