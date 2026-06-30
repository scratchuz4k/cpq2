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
import Permissions from '@/Components/Permissions.vue';

defineProps({
    permissions: Array,
    tenants: Array,
    mustVerifyEmail: {
        type: Boolean
    },
    status: {
        type: String
    }
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Profile' }]);
</script>

<template>
    <Head title="Profile" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Profile" :breadcrumbs="items">
                <template #buttons>
                    <form @submit.prevent="submit">
                        <Link :href="route('logout')" method="post" as="button" class="btn btn-primary px-4"
                            >Log Out
                        </Link>
                    </form>
                </template>
            </Header>
            <div class="row">
                <div class="col-4">
                    <div class="text-center">
                        <font-awesome-icon
                            class="bg-light p-5 rounded-circle text-secondary"
                            :icon="['fas', 'user']"
                            size="4x"
                        ></font-awesome-icon>
                    </div>
                    <div class="text-center mt-3">
                        <span
                            v-for="role in $page.props.auth.roles"
                            class="badge bg-secondary p-1 px-2 rounded text-white"
                            >{{ role }}</span
                        >
                        <h5 class="mt-2 mb-0">
                            {{ $page.props.auth.user.name }}
                        </h5>
                        <span>{{ $page.props.auth.user.email }}</span>
                        <p v-if="$page.props.auth.user.group" class="badge rounded-pill bg-light text-dark">
                            Current Group: {{ $page.props.auth.user.group.name }}
                        </p>
                        <hr class="login-seperator" />
                        <div>
                            <h5>Tenant Access</h5>
                            <ul class="nav flex-column">
                                <li v-for="tenant in tenants">
                                    <Link
                                        :href="
                                            route('tenant.make-current', {
                                                tenant: tenant.id
                                            })
                                        "
                                    >
                                        {{ tenant.name }} (T{{ tenant.id }})
                                    </Link>
                                </li>
                            </ul>
                        </div>
                        <div v-if="!true">
                            <hr class="login-seperator" />
                            <h5>User Permissions</h5>
                            <div class="px-2">
                                <Permissions :permissions="permissions" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-8">
                    <Tabs value="0">
                        <TabList>
                            <Tab value="0">Account Information</Tab>
                            <Tab value="1">Update Password</Tab>
                            <Tab v-if="!true" value="2">Delete Account</Tab>
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
                            <TabPanel v-if="!true" value="2">
                                <DeleteUserForm class="max-w-xl" />
                            </TabPanel>
                        </TabPanels>
                    </Tabs>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
