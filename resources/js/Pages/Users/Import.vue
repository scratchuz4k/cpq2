<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, Link} from "@inertiajs/vue3";
import {ref} from 'vue';

const props = defineProps({
    results: {
        type: Object,
        default: null
    }
})

const items = ref([
    { label: 'My Account', url: '/myaccount' },
    { label: 'Users', url: '/users' },
]);

</script>
<template>
    <Head title="Users" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Users" :breadcrumbs="items">
                <template #buttons>
                    <Link :href="route('users.index')" class="btn p-0 me-2">
                        <font-awesome-icon :icon="['fa', 'arrow-left']"></font-awesome-icon> Users
                    </Link>
                </template>
            </Header>
            <div v-if="props.results" class="bg-light p-4 mt-3">
                <h4>Import Results</h4>
                <p>Rows Processed: {{ results.processed }}</p>
                <p>Rows successfully imported: {{ results.success }}</p>
                <p>Rows Skipped: {{ results.skipped }}</p>

                <h4>Errors</h4>
                <ul v-if="results?.errors?.length > 0">
                    <li v-for="(row, index) in results.errors" :key="index">
                        {{ row.row.name || 'Unknown' }} ({{ row.row.email || 'Unknown' }}):
                        <span v-for="(error, key) in row.error" :key="key">{{ error }}</span>
                    </li>
                </ul>
                <p v-else>No errors occurred.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
