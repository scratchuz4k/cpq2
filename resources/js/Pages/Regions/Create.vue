<script setup>
import { reactive, ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputSelect from '@/Components/InputSelect.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import ToggleSwitch from 'primevue/toggleswitch';

const props = defineProps({
    merchants: Object,
    users: Object
});

const form = reactive({
    merchant: null,
    user: null,
    name: null,
    is_active: true
});

const saveRegion = () => {
    router.post(
        '/regions',
        {
            ...form,
            merchant_id: form.merchant?.id,
            user_id: form.user?.id
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
    { label: 'Regions', url: '/regions' },
    { label: 'New Region' }
]);
</script>
<template>
    <Head title="New Region" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Region" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton @click="saveRegion()">
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
                <div class="row mb-3">
                    <label for="merchant-name" class="form-label">Merchant:</label>
                    <InputSelect
                        id="region-merchant"
                        url="/merchants"
                        :fields="['name']"
                        v-model="form.merchant"
                        placeholder="Select an Merchant"
                        :query="[{ column: 'is_active', operator: 'equals', value: 1 }]"
                    />
                </div>
                <div class="row mb-3">
                    <label for="user-name" class="form-label">User:</label>
                    <InputSelect
                        id="region-user"
                        url="/users"
                        :fields="['name']"
                        v-model="form.user"
                        :is-multiple="false"
                        placeholder="Select an User"
                        :query="[{ column: 'is_active', operator: 'equals', value: 1 }]"
                    />
                </div>
                <div class="row mb-3">
                    <label for="region-name" class="form-label">Name:</label>
                    <div>
                        <input id="region-name" class="form-control" v-model="form.name" required />
                    </div>
                    <div class="text-danger" v-if="form.errors?.name">{{ form.errors?.name }}</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
