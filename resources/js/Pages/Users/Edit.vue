<script setup>
import MultiSelect from 'primevue/multiselect';
import { ref } from "vue";
import PrimaryButton from '@/Components/PrimaryButton.vue';
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
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Users', url: '/users' },
    { label: 'Edit User' },
]);

</script>
<template>

    <Head :title="'Edit User #' + user.id" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit User #' + user.id" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton @click="saveUser()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon> Save
                    </PrimaryButton>
                </template>
            </Header>
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
                    <div class="row mb-3">
                        <div class="form-check">
                            <Checkbox id="user-sso" name="user-sso" class="form-check-input" v-model:checked="user.sso" />
                            <label class="form-check-label" for="user-sso">
                                Single sign-on (SSO) login only
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
