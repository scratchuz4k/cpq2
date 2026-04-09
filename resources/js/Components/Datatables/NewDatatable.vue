<script setup>
import _ from 'lodash';
import { Link } from '@inertiajs/vue3';

import Paginator from 'primevue/paginator';
import { IconField, InputIcon, InputText } from 'primevue';

import { reactive, ref, useSlots, watch, markRaw, onMounted } from 'vue';
import axios from 'axios';
import { exportData } from '@/Composables/export.js';
import { DatatableOptions } from './DatatableOptions';
import { Api } from '../../Composables/useApi';
import { v4 as uuidv4 } from 'uuid';

const slots = useSlots();
const usedSlots = Object.keys(slots);

const tableContainer = ref(null);
const isScrolled = ref(false);

const onScroll = () => {
    isScrolled.value = tableContainer.value?.scrollLeft > 0;
};

const props = defineProps({
    id: {
        required: false,
        default: () => {
            return `${uuidv4()}`;
        }
    },
    options: DatatableOptions
});

const emit = defineEmits(['update:meta']);
const activeFilters = ref(false);
const api = reactive(new Api(props.options.route));

const form = reactive({
    filters: {},
    order: props.options.sort.order,
    orderBy: props.options.sort.orderBy,
    search: null
});

const setFilters = () => {
    form.filters = {};
    props.options.columns.forEach(
        (el) => (form.filters[el.column] = { column: el.column, value: null, operator: 'contains' })
    );
};
setFilters();

const getData = async () => {
    api.clearFilters();
    api.setColumns(
        props.options.columns
            .filter((x) => x.isSearchable)
            .filter((data) => data.column !== 'actions')
            .map((x) => x.column)
    );

    Object.values(form.filters)
        .filter((x) => x.value != null)
        .forEach((x) => {
            api.addFilter(x.column, x.operator, x.value);
        });

    props.options.query.forEach((x) => {
        api.addFilter(x.column, x.operator, x.value);
    });

    api.setOrder(form.orderBy, form.order);
    api.setSearch(form.search);
    await api.getData();
    emit('update:meta', api.meta);
};

const sortBy = (column) => {
    let changeSort = form.order == 'asc' ? 'desc' : 'asc';
    form.order = form.orderBy === column ? changeSort : form.order;
    form.orderBy = column;
};

const clearFilters = () => {
    Object.values(form.filters).forEach((x) => (x.value = null));
};

const deleteRecord = async (id) => {
    swal.fire({
        title: 'Are you sure?'
    }).then(async (result) => {
        if (['cancel', 'backdrop'].includes(result.dismiss)) {
            return;
        }
        try {
            let res = await axios.delete('/api/' + props.options.route + '/' + id);
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
        filters: JSON.stringify([
            ...Object.values(form.filters).filter((x) => x.value != null),
            ...props.options.query
        ]),
        search: form.search,
        columns: columns
    };

    exportData(filters, '/' + props.options.route);

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
        api.meta.page = 1;
        getData();
    }
);

const debouncedSearch = _.debounce(function () {
    api.meta.page = 1;
    getData();
}, 500);

watch(
    () => [form.filters, form.search],
    () => {
        api.isLoading = true;
        debouncedSearch();
    },
    { deep: true }
);

onMounted(() => {
    debouncedSearch();
});
</script>
<template>
    <div class="datatable-header d-flex justify-content-between py-3">
        <IconField>
            <InputIcon>
                <font-awesome-icon :icon="['fas', 'search']" />
            </InputIcon>
            <InputText
                :id="props.options.id + '-datatable-search'"
                v-model="form.search"
                type="search"
                placeholder="Search"
                size="small"
            />
        </IconField>
        <div class="d-flex">
            <button
                type="button"
                class="btn btn-sm d-flex justify-content-center align-items-center p-0 px-2 me-2"
                :class="[activeFilters ? 'btn-primary' : 'btn-outline-primary']"
                @click="() => (activeFilters = !activeFilters)"
            >
                <font-awesome-icon :icon="['fas', 'filter']" class="me-1" />
                <p class="small">Filter</p>
            </button>
            <button
                v-if="activeFilters"
                type="button"
                class="btn btn-outline-primary btn-sm d-flex justify-content-center align-items-center p-0 px-2 me-2"
                :severity="Object.values(form.filters).find((x) => x.value) ? 'primary' : 'secondary'"
                @click="clearFilters()"
            >
                <font-awesome-icon :icon="['fas', 'eraser']" class="me-1" />
                <p class="small">Clear</p>
            </button>
            <button
                v-if="props.options.allow.export"
                :disabled="!api.items.length || exportDisabled"
                :severity="'secondary'"
                class="btn btn-outline-primary btn-sm d-flex justify-content-center align-items-center p-0 px-2 me-2"
                @click="getExportData()"
                type="button"
            >
                <font-awesome-icon :icon="['fas', 'file-export']" class="me-1" />
                <p class="small">Export</p>
            </button>
        </div>
    </div>

    <div ref="tableContainer" class="datatable-content" :class="{ 'is-scrolled': isScrolled }" @scroll="onScroll">
        <table class="w-100" :id="props.options.id">
            <thead>
                <tr>
                    <th
                        v-for="(data, key) in props.options.columns.filter((x) => !x.isHidden)"
                        :key="key"
                        class="datatable-table-header"
                        :style="['position: sticky; top: -1px;', data.isSticky ? 'z-index: 3' : 'z-index: 2']"
                        :class="[
                            'datatable-col',
                            data.isSticky ? `datatable-col-sticky datatable-col-sticky-${key + 1}` : '',
                            key === props.options.columns.filter((x) => x.isSticky).length - 1
                                ? 'datatable-col-last-sticky'
                                : ''
                        ]"
                    >
                        <div
                            v-if="data.column != 'actions'"
                            class="d-flex justify-content-between align-items-center w-100 pe-3"
                        >
                            <p
                                class="datatable-table-header-text d-inline-flex align-items-center"
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
                            </p>
                            <DatatableFilter
                                v-if="activeFilters"
                                v-model="form.filters[data.column]"
                                :options="props.options"
                                :index="key"
                            ></DatatableFilter>
                        </div>
                    </th>
                    <th
                        v-if="props.options.allow.update || props.options.allow.link || props.options.allow.destroy"
                        class="datatable-table-header"
                        :class="['datatable-col', 'p-0']"
                        style="position: sticky; top: -1px; z-index: 2"
                    ></th>
                </tr>
            </thead>
            <template v-if="!api.isLoading">
                <tbody>
                    <tr v-for="(item, index) in api.items" :key="index">
                        <td
                            v-for="(data, key) in props.options.columns.filter((x) => !x.isHidden)"
                            :style="data.style"
                            :key="key"
                            class="datatable-table-column"
                            :class="[
                                'datatable-col',
                                data.isSticky ? `datatable-col-sticky datatable-col-sticky-${key + 1}` : '',
                                key === props.options.columns.filter((x) => x.isSticky).length - 1
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
                            <p class="small" v-else>{{ getValue(item, data.column) }}</p>
                        </td>
                        <td
                            v-if="props.options.allow.update || props.options.allow.link || props.options.allow.destroy"
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

                                <template v-if="props.options.allow.destroy">
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
            </template>
            <tbody v-else>
                <tr>
                    <td colspan="100%" class="text-center">
                        <font-awesome-icon :icon="['fas', 'spinner']" spin size="2x" class="mt-3" />
                    </td>
                </tr>
            </tbody>
            <tbody v-if="!api.items.length && !api.isLoading">
                <tr>
                    <td colspan="100%">
                        <p class="w-full text-center py-2">No records found</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <Paginator
        v-model:first="api.meta.first"
        :rows="api.meta.limit"
        :totalRecords="api.meta.total"
        :rowsPerPageOptions="[10, 20, 50]"
        @page="api.handlePaginator"
    >
    </Paginator>
</template>
<style scoped>
.datatable-header {
    background-color: white;
}
.datatable-content {
    background-color: white;
    min-height: 20vh;
    max-height: 60vh;
    overflow: auto;
}
.datatable-table-header {
    background-color: #e8e8e8;
    border-top: 1px solid #dee2e6;
}

.datatable-table-header-text {
    font-weight: 500;
    font-size: 12px;
    line-height: 20px;
    vertical-align: bottom;
    color: #646464;
}

.datatable-table-column {
    background-color: white;
}

.datatable-col {
    box-sizing: border-box;
    padding: 0.5rem;
    vertical-align: top;
    border-bottom: 1px solid #dee2e6;
    width: 150px !important;
}

.datatable-col-sticky {
    vertical-align: top;
    min-width: 150px !important;
    max-width: 150px !important;
}

.datatable-col-sticky-1 {
    position: sticky;
    left: 0;
    z-index: 2;
}
.datatable-col-sticky-2 {
    position: sticky;
    left: 150px;
    z-index: 2;
}
.datatable-col-sticky-3 {
    position: sticky;
    left: 300px;
    z-index: 2;
}
.datatable-col-last-sticky:after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 10px;
    height: 100%;
    transition: all 0.3s ease;
    pointer-events: none;
}

.is-scrolled .datatable-col-last-sticky:after {
    box-shadow: inset 10px 0 8px -8px #0003;
}
</style>
