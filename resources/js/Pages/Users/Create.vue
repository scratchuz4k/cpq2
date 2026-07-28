<script setup>
import { computed, reactive, ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { integerOnly, checkValue } from '@/Composables/input.js';
import InputSelect from '@/Components/InputSelect.vue';
import ToggleSwitch from 'primevue/toggleswitch';
import InputSelectMultiple from '@/Components/InputSelectMultiple.vue';

const page = usePage();

const props = defineProps({
    roles: Object
});

const form = reactive({
    name: null,
    email: null,
    password: null,
    role: null,
    role_id: null,
    groups: [],
    group_ids: [],
    commission_rate: 0,
    sso: false,
    is_active: true
});

const saveUser = () => {
    form.role_id = form.role?.id;
    form.group_ids = form.groups.map((group) => group.id);

    router.post(
        `/users`,
        {
            ...form
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
    { label: 'Settings', url: '/settings' },
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
                    <input
                        id="user-password"
                        type="password"
                        autocomplete="off"
                        class="form-control"
                        v-model="form.password"
                        required
                    />
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
                    <label for="user-group" class="form-label">Group:</label>
                    <InputSelectMultiple
                        id="user-group"
                        v-model="form.groups"
                        url="/groups"
                        :fields="['name']"
                        :placeholder="'Select an Group'"
                    >
                    </InputSelectMultiple>
                </div>
                <div class="mb-3">
                    <label for="user-commission-rate" class="form-label">Commission Rate:</label>
                    <div class="input-group p-0">
                        <input
                            id="user-commission-rate"
                            type="number"
                            max="100"
                            @keydown="integerOnly"
                            @input="checkValue"
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
                    <div class="form-check p-0">
                        <Checkbox
                            inputId="user-sso"
                            name="user-sso"
                            class="form-check-input"
                            v-model:checked="form.sso"
                        />
                        <label class="form-check-label ms-2" for="user-sso"> Single sign-on (SSO) login only </label>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
