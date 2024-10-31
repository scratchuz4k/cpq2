<script setup>
import {reactive, ref} from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from "@inertiajs/vue3";
import Permissions from "@/Components/Permissions.vue";
import MultiSelect from 'primevue/multiselect';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { hasRole } from '@composables/useAuth'

const props = defineProps({
    user: Object,
    roles: Object,
    managers: Object,
    permissions: Array,
    tenants: Array
})

const page = usePage()
const form = reactive({
    parent_id: props.user.parent_id ?? 0,
    role_id: props.user.roles.length ? props.user.roles[0].id : null,
    tenants: props.user.tenants.map((item) => item.id)
})

const role_id = ref(props.user.roles.length ? props.user.roles[0].id : { id: null})
const saveUser = () => {
    props.user.role = [role_id.value]

    router.put(`/users/${props.user.id}`, {
            ...props.user,
            ...form
        },
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
                        <select class="form-select" v-model="form.role_id"  @change="user.commission_rate = 0">
                            <option v-for="tempRole in roles" :value="tempRole.id">{{ tempRole.name }}</option>
                        </select>
                    </div>
                    <div v-if="roles.find((item) => item.id === form.role_id).name === 'Sales Rep'" class="row mb-3">
                        <label for="user-commission-rate" class="form-label">Commission Rate:</label>
                        <div class="input-group p-0">
                            <input id="user-commission-rate" type="number" class="form-control" v-model="user.commission_rate">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="user-name" class="form-label">Manager:</label>
                        <select class="form-select" v-model="form.parent_id">
                            <option value="0">No Manager</option>
                            <option v-for="manager in managers" :value="manager.id">{{ manager.name }}</option>
                        </select>
                    </div>
                    <div class="row mb-3" v-if="hasRole('Super Admin')">
                        <label for="user-name" class="form-label">Tenants:</label>
                        <MultiSelect v-model="form.tenants" :options="tenants" optionLabel="name" optionValue="id">
                            <template #option="slotProps">
                                <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option.name }}
                                </div>
                            </template>
                        </MultiSelect>
                    </div>
                    <div class="row mb-3">
                        <div class="form-check p-0">
                            <input id="user-sso " type="checkbox" class="form-check-input" v-model="user.sso">
                            <label class="form-check-label ms-2" for="user-sso">
                                Single sign-on (SSO) login only
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <h2>User Permissions</h2>
                    <Permissions :permissions="permissions" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
