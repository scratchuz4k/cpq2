<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Panel from 'primevue/panel';

const props = defineProps({
    importObject: Object
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Imports', url: '/imports' }, { label: 'Show Import' }]);
</script>
<template>
    <Head :title="'Show Import #' + importObject.id" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Show Import #' + importObject.id" :breadcrumbs="items">
                <template #buttons>
                    <Link class="btn btn-primary" :href="route('imports.index')">
                        <font-awesome-icon class="me-2" :icon="['fa', 'arrow-left']"></font-awesome-icon> Back
                    </Link>
                </template>
            </Header>

            <Panel class="mt-3" header="Import Data:" :toggleable="true">
                <p><b>File name:</b> {{ props.importObject?.name }}</p>
                <p><b>Status:</b> {{ props.importObject?.status }}</p>
                <p><b>Overwrite flag:</b> {{ props.importObject?.overwrite }}</p>
                <p><b>Imported by:</b> {{ props.importObject.user.name }}</p>
                <p><b>Imported on:</b> {{ format.date(props.importObject?.created_at) }}</p>
            </Panel>

            <Panel class="mt-3" header="Import Results:" :toggleable="true">
                <p><b>Rows Processed:</b> {{ props.importObject?.data?.processed }}</p>
                <p><b>Rows successfully imported:</b> {{ props.importObject?.data?.success }}</p>
                <p><b>Rows Skipped:</b> {{ props.importObject?.data?.skipped }}</p>
                <br />
                <p class="fw-semibold">Errors</p>
                <ul v-if="props.importObject?.errors?.length > 0">
                    <li v-for="(row, index) in results.errors" :key="index">
                        {{ row.row.name || 'Unknown' }} ({{ row.row.email || 'Unknown' }}):
                        <span v-for="(error, key) in row.error" :key="key">{{ error }}</span>
                    </li>
                </ul>
                <p v-else>No errors occurred.</p>
            </Panel>
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
