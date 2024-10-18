<script setup>
import MultiSelect from 'primevue/multiselect';
import { ref } from "vue";
import Button from 'primevue/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";
import { hasRole } from '@composables/useAuth'

const props = defineProps({
    user: Object,
    roles: Object,
    managers: Object,
    role_id: Number,
    tenants: Array
})

const saveUser = () => {
    props.user.tenants = props.user.tenants
    router.put(`/users/${props.user.id}`, props.user,
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
            {{ role }}
            <div class="row mb-3">
                <div class="col-6">
                    <div class="row mb-3">
                        <label for="user-name" class="form-label">Name:</label>
                        <input id="user-name" class="form-control" v-model="user.name" required>
                    </div>
                    <div class="row mb-3">
                        <label for="user-email" class="form-label">Email:</label>
                        <input id="user-email" type="email" class="form-control" v-model="user.email" required>
                    </div>
                    <div class="row mb-3">
                        <label for="user-password" class="form-label">Password:</label>
                        <input id="user-password" type="password" class="form-control" v-model="user.password" required>
                    </div>
                    <div class="row mb-3">
                        <label for="supplier-name" class="form-label">Role:</label>
                        <select class="form-select" v-model="user.role">
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
        </div>
    </AuthenticatedLayout>
</template>
