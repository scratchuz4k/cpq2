<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
const props = defineProps({
    users: {
        type: Array,
        default: []
    },
    solutions: {
        type: Array,
        default: []
    },
    accounts: {
        type: Array,
        default: []
    },
    status: {
        type: Array,
        default: []
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <h1 id="welcome-msg">Welcome {{ $page.props.auth.user.name }}<span>.</span></h1>

        <div id="dashboard-panel" class="panel">
            <div class="panel-body" style="min-height: 400px">
                <p>This is your dashboard. Use the navigation menu to access different sections of the application.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
