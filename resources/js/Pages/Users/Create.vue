<script setup>
import { computed, reactive, ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Select from 'primevue/select';
import InputSelect from '@/Components/InputSelect.vue';
import ToggleSwitch from 'primevue/toggleswitch';

const page = usePage();

const props = defineProps({
    roles: Object,
    managers: Object,
    parent_id: Number
});

const form = reactive({
    name: null,
    email: null,
    password: null,
    role: null,
    role_id: null,
    parent: props.parent_id ? props.managers.find((user) => user.id === props.parent_id) : null,
    commission_rate: 0,
    sso: false,
    is_active: true
});

const saveUser = () => {
    form.role_id = form.role?.id;
    router.post(
        `/users`,
        {
            ...form,
            parent_id: form.parent?.id
        },
        {
            onSuccess: (page) => {
                notyf.success('Created with Success');
            },
            onError: (errors) => {
                let string = '';
                Object.keys(errors).forEach((key) => {
                    string += errors[key] + '<br>';
                });
                notyf.error(string);
            }
        }
    );
};
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Users', url: '/users' },
    { label: 'New User' }
]);
</script>
<template>
    <Head title="New User" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New User" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-user" @click="saveUser()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="col-6">
                <div class="mb-3 d-flex flex-column">
                    <label class="form-label" for="active-filter"> Active: </label>
                    <ToggleSwitch id="active-filter" v-model="form.is_active">
                        <template #handle>
                            <font-awesome-icon
                                :icon="form.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']"
                                class="text-primary"
                            />
                        </template>
                    </ToggleSwitch>
                </div>
                <div class="mb-3">
                    <label for="user-name" class="form-label">Name:</label>
                    <input id="user-name" class="form-control" v-model="form.name" required />
                    <div class="text-danger" v-if="form.errors?.name">{{ form.errors?.name }}</div>
                </div>
                <div class="mb-3">
                    <label for="user-email" class="form-label">Email:</label>
                    <input id="user-email" type="email" class="form-control" v-model="form.email" required />
                    <div class="text-danger" v-if="form.errors?.email">{{ form.errors?.email }}</div>
                </div>
                <div class="mb-3">
                    <label for="user-password" class="form-label">Password:</label>
                    <input id="user-password" type="password" class="form-control" v-model="form.password" required />
                    <div class="text-danger" v-if="form.errors?.password">{{ form.errors?.password }}</div>
                </div>
                <div class="mb-3">
                    <label for="user-role" class="form-label">Role:</label>
                    <InputSelect
                        id="user-role"
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
                            v-model="form.commission_rate"
                        />
                        <div class="text-danger" v-if="form.errors?.commission_rate">
                            {{ form.errors?.commission_rate }}
                        </div>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="user-manager" class="form-label">Manager:</label>
                    <div class="input-group p-0">
                        <InputSelect
                            id="user-manager"
                            url="/users"
                            :fields="['name']"
                            class="w-100"
                            v-model="form.parent"
                            placeholder="Select an Manager"
                            :query="[{ column: 'is_active', operator: 'equals', value: 1 }]"
                        />
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check p-0">
                        <Checkbox id="user-sso" name="user-sso" class="form-check-input" v-model:checked="form.sso" />
                        <label class="form-check-label ms-2" for="user-sso"> Single sign-on (SSO) login only </label>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
