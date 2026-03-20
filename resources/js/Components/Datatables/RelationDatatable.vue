<script setup>
import _ from 'lodash';
import { Link } from '@inertiajs/vue3';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Paginator from 'primevue/paginator';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';

import { reactive, ref, useSlots, watch } from 'vue';
import queryString from 'qs';
import axios from 'axios';
import { can, hasRole } from '@/Composables/useAuth.js';
import pluralize from 'pluralize';
import { exportData } from '@/Composables/export.js';
import { DatatableOptions } from './DatatableOptions';
import Datatable from './Datatable.vue';

const slots = useSlots();
const usedSlots = Object.keys(slots);

const props = defineProps({
    options: DatatableOptions
});

const activeFilters = ref(false);
const items = ref([]);
const global = ref(null);

const has_export_permission =
    can(`export ${pluralize.singular(props.options.route.replace(/^\//, ''))}`) || hasRole('Super Admin');

const form = reactive({
    filters: [],
    order: props.options.sort.order,
    orderBy: props.options.sort.orderBy,
    limit: 20,
    page: 1,
    total: 0,
    first: 1,
    search: null
});

const getData = async () => {
    let string = queryString.stringify({
        ...form,
        page: form.page,
        columns: props.options.columns
            .filter((column) => column.data !== 'actions')
            .map((x) => x.data)
            .join(','),
        filters: [...form.filters.filter((x) => x.value != null), ...props.options.query],
        search: form.search
    });

    try {
        let res = await axios.get('/api/' + props.options.route + '?' + string);
        items.value = res.data.data;
        form.total = res.data.meta?.total ?? res.data.total;
        form.first = (form.page - 1) * form.limit;
    } catch (ex) {
        notyf.error(ex.response.data.message);
    }
};

const sortBy = (column) => {
    let changeSort = form.order == 'asc' ? 'desc' : 'asc';
    form.order = form.orderBy === column ? changeSort : form.order;
    form.orderBy = column;
};

const handlePaginator = (ev) => {
    form.limit = ev.rows;
    form.page = ev.page + 1;
    getData();
};
const clearFilters = () => {
    form.filters.forEach((x) => (x.value = null));
};
const deleteRecord = async (id) => {
    swal.fire({
        title: 'Are you sure?'
    }).then(async (result) => {
        if (['cancel', 'backdrop'].includes(result.dismiss)) {
            return;
        }
        try {
            let res = await axios.delete('/api' + props.options.route + '/' + id);
            getData();
            notyf.success('Removed with success');
        } catch (err) {
            notyf.error(err?.response?.data?.message ?? 'Something went wrong');
        }
    });
};

const getValue = (item, key) => {
    let items = key.split('.');
    let result = item;

    for (let i = 0; i < items.length; i++) {
        const prop = items[i];

        // Check if the current property exists in the current object
        if (result && typeof result === 'object' && prop in result) {
            result = result[prop];
            // Access the nested object using the current property
            if (Array.isArray(result) && i < items.length - 1) {
                result = result.length > 0 ? result[0] : undefined;
            }
        } else {
            // Property not found, return undefined
            return undefined;
        }
    }

    return result;
};

const exportDisabled = ref(false);

const getExportData = async () => {
    if (exportDisabled.value) return;
    exportDisabled.value = true;

    let columns = props.options.columns
        .filter((column) => column.data !== 'actions')
        .map((x) => x.data)
        .join(',');

    let filters = {
        filters: JSON.stringify([...form.filters.filter((x) => x.value != null), ...props.options.query]),
        search: form.search,
        columns: columns
    };

    exportData(filters, props.options.route);

    setTimeout(() => {
        exportDisabled.value = false;
    }, 5000);
};

emitter.on('datatable-refresh' + (props.options.id ? ':' + props.options.id : ''), () => {
    getData();
});

watch(
    () => [form.order, form.orderBy],
    function () {
        form.page = 1;
        getData();
    }
);

watch(
    [form.filters, form.search],
    _.debounce(function () {
        form.page = 1;
        getData();
    }, 200)
);

getData();
</script>
<template>
    <div class="d-flex justify-content-between mt-3">
        <div>
            <h2>Products (1)</h2>
        </div>
        <div>
            <button class="btn btn-primary">
                <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                New Product
            </button>
        </div>
    </div>
    <div class="d-flex justify-content-between">
        <div>
            <Button
                :severity="activeFilters ? 'primary' : 'secondary'"
                type="button"
                :class="['me-2']"
                @click="() => (activeFilters = !activeFilters)"
            >
                <font-awesome-icon :icon="['fas', 'filter']" />
            </Button>
            <Button
                v-if="activeFilters"
                type="button"
                :class="['me-2']"
                :severity="form.filters.find((x) => x.value) ? 'primary' : 'secondary'"
                @click="clearFilters()"
            >
                <font-awesome-icon :icon="['fas', 'eraser']" />
            </Button>
            <Button
                v-if="has_export_permission"
                :disabled="!items.length || exportDisabled"
                :severity="'secondary'"
                :class="['me-2']"
                @click="getExportData()"
                type="button"
            >
                <font-awesome-icon :icon="['fas', 'file-export']" />
            </Button>
        </div>
        <IconField>
            <InputIcon>
                <font-awesome-icon :icon="['fas', 'search']" />
            </InputIcon>
            <InputText id="datatable-search" v-model="global" type="search" placeholder="Keyword Search" />
        </IconField>
    </div>
    <div v-if="items.length" style="max-height: 60vh; overflow: auto">
        <table :id="props.options.id" class="table">
            <thead>
                <tr>
                    <th
                        v-for="(data, key) in props.options.columns.filter((x) => !x.hide)"
                        :key="key"
                        :class="[
                            'datatable-col',
                            data.sticky ? `datatable-col-sticky datatable-col-sticky-${key + 1}` : '',
                            key === props.options.columns.filter((x) => x.sticky).length - 1
                                ? 'datatable-col-last-sticky'
                                : ''
                        ]"
                    >
                        <div
                            v-if="data.column != 'actions'"
                            class="d-flex justify-content-between align-items-center w-100 pe-3 position-relative"
                        >
                            <b
                                class="column-title sortable d-inline-flex align-items-center"
                                @click="sortBy(data.column)"
                                :style="{
                                    textDecoration: data.column == form.orderBy ? 'underline' : 'none'
                                }"
                            >
                                {{ data.title }}
                                <span class="text-center" style="width: 20px">
                                    <template v-if="data.column == form.orderBy">
                                        <font-awesome-icon v-if="form.order == 'desc'" :icon="['fas', 'sort-down']" />
                                        <font-awesome-icon v-else :icon="['fas', 'sort-up']" />
                                    </template>
                                </span>
                            </b>
                            <!-- <DatatableFilter v-if="activeFilters"></DatatableFilter> -->
                        </div>
                    </th>
                    <th
                        v-if="props.options.allow.update || props.options.allow.link || props.options.allow.delete"
                        :class="['datatable-col', 'p-0']"
                    ></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, index) in items" :key="index">
                    <td
                        v-for="(data, key) in props.options.columns.filter((x) => !x.hide)"
                        :style="data.style"
                        :key="key"
                        :class="[
                            'datatable-col',
                            data.sticky ? `datatable-col-sticky datatable-col-sticky-${key + 1}` : '',
                            key === props.options.columns.filter((x) => x.sticky).length - 1
                                ? 'datatable-col-last-sticky'
                                : ''
                        ]"
                    >
                        <slot
                            v-if="usedSlots.includes(data.column)"
                            :index="index"
                            :data="item"
                            :name="data.column"
                            :value="getValue(item, data.column)"
                            :id="`${data.column}-${index}`"
                        />
                        <template v-else>{{ getValue(item, data.column) }}</template>
                    </td>
                    <td
                        v-if="props.options.allow.update || props.options.allow.link || props.options.allow.delete"
                        :class="['datatable-col', 'p-0']"
                    >
                        <div class="d-flex justify-content-center">
                            <template v-if="props.options.allow.update">
                                <Link
                                    :id="'datatable-btn-' + index + '-edit'"
                                    :href="props.options.route + '/' + item.id + '/edit'"
                                    class="btn p-2 mx-1 border-0"
                                >
                                    <font-awesome-icon :icon="['fas', 'edit']" />
                                </Link>
                            </template>

                            <template v-if="props.options.allow.link">
                                <Link
                                    :id="'datatable-btn-' + index + '-show'"
                                    :href="props.options.route + '/' + item.id"
                                    class="btn p-2 mx-1 border-0"
                                >
                                    <font-awesome-icon :icon="['fas', 'eye']" />
                                </Link>
                            </template>

                            <template v-if="props.options.allow.delete">
                                <button
                                    type="button"
                                    :id="'datatable-btn-' + index + '-delete'"
                                    class="btn p-2 mx-1 border-0"
                                    @click="deleteRecord(item.id)"
                                >
                                    <font-awesome-icon :icon="['fas', 'trash-alt']" />
                                </button>
                            </template>
                        </div>
                    </td>
                </tr>
            </tbody>
            <tfoot></tfoot>
        </table>
    </div>
    <div v-else class="d-flex justify-content-center align-items-center" style="height: 200px">
        <div class="text-center my-1">No records found</div>
    </div>
    <Paginator
        v-model:first="form.first"
        :rows="form.limit"
        :totalRecords="form.total"
        :rowsPerPageOptions="[20, 30, 50]"
        @page="handlePaginator"
    >
    </Paginator>
</template>
<style scoped>
.datatable-col {
    padding: 0.75rem;
    vertical-align: middle;
}

.datatable-col-sticky {
    padding: 0.75rem;
    vertical-align: middle;
    min-width: 150px !important;
}

.datatable-col-sticky-1 {
    position: sticky;
    left: 0;
    background: #fff;
    z-index: 2;
}
.datatable-col-sticky-2 {
    position: sticky;
    left: 150px;
    background: #fff;
    z-index: 2;
}
.datatable-col-sticky-3 {
    position: sticky;
    left: 300px;
    background: #fff;
    z-index: 2;
}

.datatable-col-last-sticky {
    border-right: 1px solid #dee2e6;
    box-shadow: 2px 0 8px -2px rgba(0, 0, 0, 0.15);
}
</style>
