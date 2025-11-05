<script setup>
import { reactive, ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Permissions from '@/Components/Permissions.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ToggleSwitch from 'primevue/toggleswitch';
import Select from 'primevue/select';
import InputSelect from '@/Components/InputSelect.vue';

const props = defineProps({
    user: Object,
    roles: Object,
    permissions: Array
});

const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get() {
        return props.user;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

const page = usePage();
const form = reactive({
    role: props.user.roles.length ? props.user.roles[0] : null,
    role_id: props.user.roles.length ? props.user.roles[0].id : null
});

const saveUser = () => {
    proxyValue.value.role_id = form.role ? [form.role.id] : [];

    router.put(`/users/${proxyValue.value.id}`, proxyValue.value, {
        onSuccess: (page) => {
            notyf.success('Updated with Success');
        },
        onError: (errors) => {
            let string = '';
            Object.keys(errors).forEach((key) => {
                string += errors[key] + '<br>';
            });
            notyf.error(string);
        }
    });
};
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Users', url: '/users' },
    { label: 'Edit User' }
]);
</script>
<template>
    <Head :title="'Edit User #' + user.id" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit User #' + user.id" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-user" @click="saveUser()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="row mb-3">
                <div class="col-6">
                    <div class="mb-3 d-flex flex-column">
                        <label class="form-label" for="active-filter"> Active: </label>
                        <ToggleSwitch id="active-filter" v-model="proxyValue.is_active">
                            <template #handle>
                                <font-awesome-icon
                                    :icon="proxyValue.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']"
                                    class="text-primary"
                                />
                            </template>
                        </ToggleSwitch>
                    </div>
                    <div class="mb-3">
                        <label for="user-name" class="form-label">Name:</label>
                        <input id="user-name" class="form-control" v-model="proxyValue.name" required />
                    </div>
                    <div class="mb-3">
                        <label for="user-email" class="form-label">Email:</label>
                        <input id="user-email" type="email" class="form-control" v-model="proxyValue.email" required />
                    </div>
                    <div class="mb-3">
                        <label for="user-password" class="form-label">Password:</label>
                        <input
                            id="user-password"
                            type="password"
                            class="form-control"
                            v-model="proxyValue.password"
                            required
                        />
                    </div>

                    <div class="mb-3">
                        <label for="user-role" class="form-label">Role:</label>
                        <InputSelect
                            id="account_id"
                            v-model="form.role"
                            url="/roles"
                            :fields="['name']"
                            :placeholder="'Select an Role'"
                        >
                        </InputSelect>
                    </div>
                    <div class="mb-3">
                        <label for="user-commission-rate" class="form-label">Commission Rate:</label>
                        <div class="input-group p-0">
                            <input
                                id="user-commission-rate"
                                type="number"
                                class="form-control"
                                v-model="user.commission_rate"
                            />
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="mb-3" v-if="user.group">
                        <label for="user-group" class="form-label">Group:</label>
                        <input id="user-group" disabled class="form-control" v-model="user.group.name" />
                    </div>
                    <div class="mb-3">
                        <div class="form-check p-0">
                            <input id="user-sso" type="checkbox" class="form-check-input" v-model="user.sso" />
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
