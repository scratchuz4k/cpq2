<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    results: {
        type: Object,
        default: null
    }
});

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Users', url: '/users' },
    { label: 'Import Users' }
]);
</script>
<template>
    <Head title="Users" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Users" :breadcrumbs="items">
                <template #buttons>
                    <Link class="btn btn-primary" :href="route('users.index')">
                        <font-awesome-icon class="me-2" :icon="['fa', 'arrow-left']"></font-awesome-icon> Back
                    </Link>
                </template>
            </Header>
            <div v-if="props.results" class="bg-light p-4 mt-3">
                <h4>Import Results</h4>
                <p><b>Rows Processed:</b> {{ results.processed }}</p>
                <p><b>Rows successfully imported:</b> {{ results.success }}</p>
                <p><b>Rows Skipped:</b> {{ results.skipped }}</p>
                <br />
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

<style scoped>
li {
    counter-increment: item;
    margin-bottom: 5px;
}
li:before {
    margin-right: 10px;
    content: counter(item);
    background: var(--color-purple-light);
    border-radius: 100%;
    color: var(--dark-purple);
    width: 25px;
    height: 25px;
    line-height: 24px;
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    display: inline-block;
}
</style>
