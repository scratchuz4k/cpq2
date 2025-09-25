<script setup>
import { reactive, ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    role: Object
});

const form = reactive({
    name: props.role.name,
    temp_permissions: props.role.permissions,
    permissions: []
});

const saveRole = () => {
    form.permissions = form.temp_permissions.map((x) => x.id);
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
    { label: 'New Role' }
]);
</script>
<template>
    <Head title="New Role" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Role" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton @click="saveRole()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="row mb-3">
                <div class="col-6">
                    <label for="region-name" class="form-label">Name:</label>
                    <div>
                        <input id="region-name" class="form-control" v-model="form.name" required />
                    </div>
                    <div class="text-danger" v-if="form.errors?.name">{{ form.errors?.name }}</div>
                </div>
            </div>
            <div>
                <label for="region-name" class="form-label">Permissions:</label>
                <PermissionsTree v-model="form.temp_permissions"></PermissionsTree>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
