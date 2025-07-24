<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Select from 'primevue/select';
import ToggleSwitch from 'primevue/toggleswitch';

const props = defineProps({
    plainTextToken: String,
    users: Array,
    token: Object
});

const toggleIsActive = async () => {
    let result = await swal.fire({
        title: 'Do you want to change the active status?'
    });

    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return false;
    }

    try {
        let res = await axios.put(`/api/tokens/` + props.token.id, {
            is_active: props.token.is_active
        });
        notyf.success('Updated with Success');
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
    }
};

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Tokens', url: '/tokens' },
    { label: 'Show Token' }
]);
</script>
<template>
    <Head :title="'Show Token #' + token.id" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Show Token #' + token.id" :breadcrumbs="items">
                <template #buttons>
                    <Link class="btn btn-primary" :href="route('tokens.index')">
                        <font-awesome-icon class="me-2" :icon="['fa', 'arrow-left']"></font-awesome-icon>
                        Back
                    </Link>
                </template>
            </Header>
            <div class="col-6">
                <div class="mb-3 d-flex flex-column">
                    <label class="form-label" for="active-filter"> Active: </label>
                    <ToggleSwitch id="active-filter" v-model="token.is_active" @change="toggleIsActive">
                        <template #handle>
                            <font-awesome-icon
                                :icon="token.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']"
                                class="text-primary"
                            />
                        </template>
                    </ToggleSwitch>
                </div>
                <div class="mb-3 alert alert-danger" v-if="plainTextToken">
                    <label for="token-name" class="form-label">Save this token for api usage:</label>
                    <p class="m-0">{{ plainTextToken }}</p>
                </div>
                <div class="mb-3">
                    <label for="token-name" class="form-label">Name:</label>
                    <input id="token-name" class="form-control" v-model="token.name" disabled />
                </div>
                <div class="mb-3">
                    <label class="form-label">User:</label>
                    <Select
                        v-model="token.tokenable_id"
                        :inputId="'token-user'"
                        :options="users"
                        class="w-100"
                        optionLabel="description"
                        optionValue="id"
                        placeholder="Select a User"
                        disabled
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
