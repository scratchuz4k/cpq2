<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Panel from 'primevue/panel';
import CountUp from 'vue-countup-v3';

const props = defineProps({
    importObject: Object,
    resources: Object
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

            <Panel class="mt-3" header="Import Details" :toggleable="true">
                <div class="mt-2 row row-cols-1 row-cols-md-2 g-3 mb-2">
                    <p><b>Imported By:</b><br />{{ props.importObject.user.name }}</p>
                    <p><b>Imported On:</b><br />{{ format.formattedDate(props.importObject.created_at) }}</p>
                    <p>
                        <b>Imported File:</b><br />
                        <a
                            v-if="props.importObject.file_id"
                            :href="props.resources.full_route"
                            :download="props.importObject.name"
                            class="btn p-0 text-decoration-underline"
                            title="Download file"
                        >
                            <font-awesome-icon :icon="['fas', 'download']" class="me-1" />
                            {{ props.importObject.name }}
                        </a>
                    </p>
                    <p>
                        <b>Overwrite Existing Products:</b><br />
                        <span class="text-secondary" v-if="props.importObject.overwrite">
                            <font-awesome-icon :icon="['fas', 'circle-check']" class="me-1 text-primary" size="lg" />
                            <i>true</i>
                        </span>
                        <span class="text-secondary" v-else>
                            <font-awesome-icon :icon="['far', 'circle-xmark']" class="me-1 text-secondary" size="lg" />
                            <i>false</i>
                        </span>
                    </p>
                    <p>
                        <b>Model:</b><br />
                        <span class="badge border border-secondary text-secondary opacity-75 lh-sm text-uppercase">
                            {{ props.importObject.model }}</span
                        >
                    </p>
                    <p>
                        <b>Status:</b><br />
                        <span class="badge text-bg-secondary text-uppercase"> {{ props.importObject.status }}</span>
                    </p>
                </div>
            </Panel>

            <Panel class="mt-3" header="Import Results" :toggleable="true">
                <div class="mt-2 row row-cols-1 row-cols-md-3 g-3 mb-2">
                    <div class="col">
                        <div class="card py-4 bg-light border-0">
                            <div class="card-body text-center p-2 text-decoration-none">
                                <div class="fw-bold mb-1">Rows Processed</div>
                                <span class="display-6">
                                    <count-up :endVal="props.importObject.data?.processed ?? 0" />
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card py-4 bg-light border-0">
                            <div class="card-body text-center p-2 text-decoration-none">
                                <div class="fw-bold mb-1">Rows Imported</div>
                                <span class="display-6">
                                    <count-up :endVal="props.importObject.data?.success ?? 0" />
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card py-4 bg-light border-0">
                            <div class="card-body text-center p-2 text-decoration-none">
                                <div class="fw-bold mb-1">Rows Skipped</div>
                                <span class="display-6">
                                    <count-up :endVal="props.importObject.data?.skipped ?? 0" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <br />
                <p class="fw-semibold">Errors</p>
                <ul v-if="props.importObject.data?.errors?.length > 0">
                    <li class="rows" v-for="(row, index) in props.importObject.data.errors" :key="index">
                        {{ importObject.model }} {{ row.row.name || 'Unknown' }}:
                        <ul>
                            <li v-if="row.errors" v-for="(error, key) in row.errors" :key="key">
                                {{ error }}
                            </li>
                            <li v-else>{{ row.error }}</li>
                        </ul>
                    </li>
                </ul>
                <p v-else>No errors occurred.</p>
            </Panel>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.p-panel-title {
    font-size: 2rem;
    font-weight: 600;
    color: var(--dark-purple);
}

li.rows {
    counter-increment: item;
    margin-bottom: 5px;
}
li.rows:before {
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
