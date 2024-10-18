<script setup>
import MultiSelect from 'primevue/multiselect';
import { ref } from "vue";
import Button from 'primevue/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { hasRole } from '@composables/useAuth'

const page = usePage();

const props = defineProps({
    roles: Object,
    managers: Object,
    tenants: Array
})

const user = useForm({
    name: null,
    email: null,
    password: null,
    role_id: null,
    parent_id: null,
    tenants: [page.props.tenant.id]
})

const saveUser = () => {
    user.tenants = user.tenants
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
                <div class="row mb-3">
                    <label for="user-name" class="form-label">Manager:</label>
                    <select class="form-select" v-model="user.parent_id">
                        <option v-for="manager in managers" :value="manager.id">{{ manager.name }}</option>
                    </select>
                </div>
                <div class="row mb-3" v-if="hasRole('Super Admin')">
                    <label for="user-name" class="form-label">Tenants:</label>
                    <MultiSelect v-model="user.tenants" :options="tenants" optionLabel="name" optionValue="id">
                        <template #option="slotProps">
                            <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option.name }}
                            </div>
                        </template>
                    </MultiSelect>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
