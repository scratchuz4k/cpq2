<script setup>
import {reactive, ref} from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from "@inertiajs/vue3";
import Permissions from "@/Components/Permissions.vue";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Select from "primevue/select";

const props = defineProps({
    user: Object,
    roles: Object,
    managers: Object,
    permissions: Array,
})

const page = usePage()
const form = reactive({
    parent_id: props.user.parent_id ?? 0,
    role_id: props.user.roles.length ? props.user.roles[0].id : null
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
                        <Select v-model="form.role_id"
                                :inputId="'user-role'"
                                :options="roles"
                                class="w-100"
                                editable
                                optionLabel="name"
                                optionValue="id"
                                placeholder="Select an option"
                                @change="user.commission_rate = 0"
                        />
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
                        <Select v-model="form.parent_id"
                                :inputId="'user-manager'"
                                :options="managers"
                                class="w-100"
                                editable
                                optionLabel="name"
                                optionValue="id"
                                placeholder="Select an option"
                        />
                    </div>
                    <div class="row mb-3" v-if="user.group">
                        <label for="user-group" class="form-label">Group:</label>
                        <input id="user-group" disabled class="form-control" v-model="user.group.name">
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
