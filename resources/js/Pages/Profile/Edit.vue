<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
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
    { label: 'My Account', url: '/myaccount' }
]);

</script>

<template>
    <Head title="My Profile" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="My Profile" :breadcrumbs="items"></Header>
            <Tabs value="0">
                <TabList>
                    <Tab value="0">Account Information</Tab>
                    <Tab value="1">Update Password</Tab>
                    <Tab value="2">Delete Account</Tab>
                </TabList>
                <TabPanels>
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
