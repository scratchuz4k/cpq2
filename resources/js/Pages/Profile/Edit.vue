<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'My Profile' }
]);

</script>

<template>
    <Head title="My Profile" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="My Profile" :breadcrumbs="items">
                <template #buttons>
                    <Link class="btn btn-primary" :href="route('myaccount')">
                        <font-awesome-icon class="me-2" :icon="['fa', 'arrow-left']"></font-awesome-icon> Back
                    </Link>
                </template>
            </Header>
            <Tabs value="0">
                <TabList>
                    <Tab value="0">Account Information</Tab>
                    <Tab value="1">Update Password</Tab>
                    <Tab value="2">Delete Account</Tab>
                </TabList>
                <TabPanels class="mt-4">
                    <TabPanel value="0">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="max-w-xl"
                        />
                    </TabPanel>
                    <TabPanel value="1">
                        <UpdatePasswordForm class="max-w-xl" />
                    </TabPanel>
                    <TabPanel value="2">
                        <DeleteUserForm class="max-w-xl" />
                    </TabPanel>
                </TabPanels>
            </Tabs>
        </div>
    </AuthenticatedLayout>
</template>
