<script setup>
import {computed, reactive, ref} from "vue";
import Button from 'primevue/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";
import Permissions from "@/Components/Permissions.vue";

const props = defineProps({
    user: Object,
    roles: Object,
    managers: Object,
    role_id: Number,
    permissions: Array
})

const role_id = ref(props.user.roles.length ? props.user.roles[0].id : { id: null})
const saveUser = () => {

    props.user.role = [role_id.value]
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
                        <select class="form-select" v-model="role_id"  @change="user.commission_rate = 0">
                            <option v-for="tempRole in roles" :value="tempRole.id">{{ tempRole.name }}</option>
                        </select>
                    </div>
                    <div v-if="roles.find((item) => item.id === role_id).name === 'Sales Rep'" class="row mb-3">
                        <label for="user-commission-rate" class="form-label">Commission Rate:</label>
                        <div class="input-group p-0">
                            <input id="user-commission-rate" type="number" class="form-control" v-model="user.commission_rate">
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
                <div class="col-6">
                    <h2>User Permissions</h2>
                    <Permissions :permissions="permissions" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
