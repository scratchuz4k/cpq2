<script setup>
import queryString from 'query-string';

import { Link, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { reactive, ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    route: String,
    columns: Array
})
const data = ref([])

const getData = async () => {
    let res = await axios.get('/api' + props.route + '?filters=' + JSON.stringify(filters.value))
    data.value = res.data.data
}

const global = reactive({
    column: '*',
    operator: 'contains',
    value: null
})
const filters = ref([global])

watch(global, () => {
    getData()
})
getData()
</script>
<template>
    <DataTable :pt="{ table: 'table table-striped' }" :value="data" scrollable scrollHeight="500px" paginator
        :rows="10" dataKey="id">
        <template #header>
            <div class="flex justify-end">
                <input v-model="global.value" placeholder="Keyword Search" type="text" class="form-control" />
            </div>
        </template>
        <Column v-for="column in columns" :field="column.data" :header="column.title">
        </Column>
        <Column style="flex: 0 0 4rem">
            <template #body="{ data, index }">
                <Link :href="props.route + '/' + data.id" class="btn btn-secondary btn-sm me-2">
                <font-awesome-icon :icon="['fas', 'eye']" />
                </Link>
                <Link :href="props.route + '/' + data.id + '/edit'" class="btn btn-secondary btn-sm">
                <font-awesome-icon :icon="['fas', 'edit']" />
                </Link>
            </template>
        </Column>
    </DataTable>
</template>
