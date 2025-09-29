<script setup>
import { reactive, ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { hasRole } from '@/Composables/useAuth';

const props = defineProps({
    role: Object
});

const form = reactive({
    name: props.role.name,
    temp_permissions: props.role.permissions,
    permissions: [],
    parent: props.role.parent,
    parent_id: props.role.parent ? props.role.parent.id : null
});

const saveRole = () => {
    form.permissions = form.temp_permissions.map((x) => x.id);
    form.parent_id = form.parent ? form.parent.id : null;
    router.put(
        '/roles/' + props.role.id,
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
    { label: 'My Account', url: '/myaccount' },
    { label: 'Roles', url: '/roles' },
    { label: 'Edit Role' }
]);
</script>
<template>
    <Head title="Edit Role" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Edit Role" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton @click="saveRole()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <InfoBox class="mt-3">
                <small> Roles without parent are considered top-level roles </small>
            </InfoBox>

            <InfoBox v-if="hasRole(props.role.name)" severity="danger" class="mt-3">
                <small>
                    Roles without parent are considered top-level roles, this influences users and roles visibility
                </small>
            </InfoBox>
            <div class="row mt-3">
                <div class="col-6">
                    <div class="mb-3">
                        <label for="region-name" class="form-label">Name:</label>
                        <div>
                            <input id="region-name" class="form-control" v-model="form.name" required />
                        </div>
                        <div class="text-danger" v-if="form.errors?.name">{{ form.errors?.name }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="user-role" class="form-label">Parent:</label>
                        <InputSelect
                            id="account_id"
                            v-model="form.parent"
                            url="/roles"
                            :fields="['name']"
                            :placeholder="'Select an Role'"
                        >
                        </InputSelect>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <label for="region-name" class="form-label">Permissions:</label>
                <PermissionsTree v-model="form.temp_permissions"></PermissionsTree>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
