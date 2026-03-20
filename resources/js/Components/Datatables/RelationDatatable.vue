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

const slots = useSlots();
const usedSlots = Object.keys(slots);

const props = defineProps({
    options: DatatableOptions
});

const activeFilters = ref(false);
const data = ref([]);
const emptyData = ref(true);
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
        data.value = res.data.data;
        emptyData.value = !data.value.length;
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
    <DataTable
        :tableProps="{ id: props.options.id }"
        :value="data"
        :rows="form.limit"
        stripedRows
        :reorderableColumns="false"
    >
        <template #header>
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
                        :disabled="emptyData || exportDisabled"
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
        </template>
        <Column
            v-for="(data, key) in props.options.columns.filter((x) => !x.hide)"
            :field="data.column"
            :style="data.style"
        >
            <template #header v-if="data.column != 'actions'">
                <div class="d-flex justify-content-between align-items-center w-100 pe-3 position-relative">
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
                    <div v-if="activeFilters" style="position: absolute; right: 0; z-index: 10">
                        <button class="btn btn-sm btn-filter">
                            <font-awesome-icon :icon="['fas', 'filter']" />
                        </button>
                        <div class="filter-content" :class="{ last: key + 1 == columns.length }">
                            <template v-if="Object.keys(props.options.filters).includes(data.column)">
                                <div class="my-1" v-if="props.options.filters[data.column].type === 'checkbox'">
                                    <div class="form-check p-0">
                                        <Checkbox
                                            inputId="active-filter"
                                            class="form-check-input"
                                            v-model="form.filters[key].value"
                                            :checked="!!form.filters[key].value"
                                        />
                                        <label class="form-check-label ms-2" for="active-filter"> Active </label>
                                    </div>
                                </div>
                                <div v-if="props.options.filters[data.column].type === 'options'" class="mb-3">
                                    <label class="form-label" :for="`filter-value-${key}`">Options</label>
                                    <select
                                        :id="`filter-value-${key}`"
                                        class="form-select w-100"
                                        v-model="form.filters[key].value"
                                    >
                                        <option :value="null">All</option>
                                        <option
                                            v-for="option in filters[data.column].options"
                                            :value="typeof option === 'object' ? option.value : option"
                                        >
                                            {{ typeof option === 'object' ? option.label : option }}
                                        </option>
                                    </select>
                                </div>
                            </template>
                            <div v-else class="mb-3">
                                <label class="form-label" :for="`filter-value-${key}`">Search</label>
                                <input
                                    :id="`filter-value-${key}`"
                                    type="search"
                                    class="form-control me-2 form-control-sm"
                                    v-model="form.filters[key].value"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template #body="{ data, field, index }">
                <slot
                    v-if="usedSlots.includes(field)"
                    :index="index"
                    :data="data"
                    :name="field"
                    :value="getValue(data, field)"
                    :id="`${field}-${index}`"
                />
                <template v-else>{{ getValue(data, field) }}</template>
            </template>
        </Column>
        <Column v-if="allow?.update || allow?.link || allow?.delete" :class="['p-0']" style="width: auto">
            <template #body="{ data, index }">
                <div class="d-flex justify-content-center">
                    <template v-if="allow.update">
                        <Link
                            :id="'datatable-btn-' + index + '-edit'"
                            :href="props.options.route + '/' + data.id + '/edit'"
                            class="btn p-2 mx-1 border-0"
                        >
                            <font-awesome-icon :icon="['fas', 'edit']" />
                        </Link>
                    </template>

                    <template v-if="allow.link">
                        <Link
                            :id="'datatable-btn-' + index + '-show'"
                            :href="props.options.route + '/' + data.id"
                            class="btn p-2 mx-1 border-0"
                        >
                            <font-awesome-icon :icon="['fas', 'eye']" />
                        </Link>
                    </template>

                    <template v-if="allow.delete">
                        <button
                            type="button"
                            :id="'datatable-btn-' + index + '-delete'"
                            class="btn p-2 mx-1 border-0"
                            @click="deleteRecord(data.id)"
                        >
                            <font-awesome-icon icon="fas fa-trash-alt" />
                        </button>
                    </template>
                </div>
            </template>
        </Column>
        <template #empty>
            <div class="text-center my-1">No records found</div>
        </template>
    </DataTable>
    <Paginator
        v-model:first="form.first"
        :rows="form.limit"
        :totalRecords="form.total"
        :rowsPerPageOptions="[20, 30, 50]"
        @page="handlePaginator"
    >
    </Paginator>
</template>
<style>
.p-datatable {
    z-index: 1;
}

.p-datatable .p-datatable-thead {
    z-index: 1;
}

.p-datatable .p-datatable-header {
    padding: 0.75rem 0rem 0rem !important;
    border-bottom: 0 !important;
}

.p-datatable .p-datatable-table-container {
    overflow: visible !important;
}

.p-datatable tr td:not(.p-0),
.p-datatable tr th {
    padding: 0.5rem 0.5rem !important;
}

.p-datatable .p-datatable-empty-message td:not(.p-0) {
    padding: 0.25rem 0.5rem !important;
}

.p-datatable .sortable:hover {
    text-decoration: underline;
}

.p-datatable .btn-filter:hover ~ .filter-content {
    max-height: 500px;
    padding: 10px;
    border: 1px solid #e2e8f0;
}

.p-datatable .filter-content {
    max-height: 0;
    overflow: hidden;
    position: absolute;
    top: 100%;
    left: 0;
    width: 250px;
    background-color: white;
    border-radius: 5px;
    box-shadow: rgba(0, 0, 0, 0.1) 0px 4px 12px;
    padding: 0 10px;
    z-index: 9999;
}

.p-datatable .filter-content.last {
    left: auto;
    right: 0;
}

.p-datatable .filter-content:hover {
    max-height: 500px;
    padding: 10px;
    border: 1px solid #e2e8f0;
}
</style>
