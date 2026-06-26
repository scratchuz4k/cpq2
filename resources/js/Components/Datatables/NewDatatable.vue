<script setup>
import _, { chain } from 'lodash';
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
const activeColumnsDropdown = ref(false);

const onScroll = () => {
    isScrolled.value = tableContainer.value?.scrollLeft > 0;
    showTooltip.value = false;
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
const activeMenuIndex = ref(null);
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

const sortBy = (data) => {
    if (!data.isSortable) {
        return;
    }
    let changeSort = form.order == 'asc' ? 'desc' : 'asc';
    form.order = form.orderBy === data.column ? changeSort : form.order;
    form.orderBy = data.column;
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

const isArrayValue = (item, key) => {
    let items = key.split('.');
    let result = item;

    for (let i = 0; i < items.length; i++) {
        const prop = items[i];

        // Check if the current property exists in the current object
        if (result && typeof result === 'object' && prop in result) {
            result = result[prop];
            // Access the nested object using the current property
            if (Array.isArray(result) && i < items.length - 1) {
                return Array.isArray(result)
                    ? { items: result.slice(1), key: items.slice(i + 1, item.length) }
                    : { items: [], keys: [] };
            }
        } else {
            // Property not found, return undefined
            return { items: [], keys: [] };
        }
    }

    return { items: [], keys: [] };
};
const showTooltip = ref(false);
const tooltipValues = reactive({
    items: [],
    keys: []
});
const tooltipPosition = ref({ top: 0, left: 0 });

const sendTooltipValues = (ev, object) => {
    const rect = ev.target.getBoundingClientRect();
    tooltipPosition.value = {
        top: rect.bottom + window.scrollY, // Below the element
        left: rect.left + window.scrollX // Aligned with element
    };

    showTooltip.value = true;
    tooltipValues.items = object.items.slice(0, 10);
    tooltipValues.keys = object.key.join('.');
};

const exportDisabled = ref(false);

const getExportData = async () => {
    if (exportDisabled.value) return;
    exportDisabled.value = true;

    const visibleColumns = props.options.columns.filter(
        (col) => !col.isHidden && !isUserHidden(col) && col.column !== 'actions'
    );

    let filters = {
        filters: JSON.stringify([
            ...Object.values(form.filters).filter((x) => x.value != null),
            ...props.options.query
        ]),
        search: form.search,
        columns: visibleColumns.map((x) => x.column).join(','),
        column_titles: visibleColumns.map((x) => x.title).join(',')
    };

    exportData(filters, '/' + props.options.route);

    setTimeout(() => {
        exportDisabled.value = false;
    }, 5000);
};

const userHiddenColumns = ref(
    JSON.parse(localStorage.getItem('datatable_user_hidden_columns_' + props.options.id) || '[]')
);
const isUserHidden = (column) => {
    return userHiddenColumns.value.includes(column.column);
};

const toggleUserHiddenColumn = (column) => {
    const index = userHiddenColumns.value.indexOf(column.column);
    if (index > -1) {
        userHiddenColumns.value.splice(index, 1);
    } else {
        userHiddenColumns.value.push(column.column);
    }
    localStorage.setItem('datatable_user_hidden_columns_' + props.options.id, JSON.stringify(userHiddenColumns.value));
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
    window.addEventListener(
        'scroll',
        () => {
            showTooltip.value = false;
        },
        { passive: true }
    );
    var select = document.getElementById(props.options.id + '-tooltip');
    var selectColumns = document.getElementById(props.options.id + '-column-select');
    document.addEventListener('click', (ev) => {
        const isTooltipClick = Array.from(document.getElementsByClassName('tooltip-indicator')).some((x) =>
            x.contains(ev.target)
        );
        if (!select.contains(ev.target) && !isTooltipClick) {
            showTooltip.value = false;
        }

        if (!selectColumns.contains(ev.target)) {
            activeColumnsDropdown.value = false;
        }

        const isMenuClick = Array.from(document.getElementsByClassName('datatable-actions-menu')).some((x) =>
            x.contains(ev.target)
        );
        if (!isMenuClick) {
            activeMenuIndex.value = null;
        }
    });
});
</script>
<template>
    <div class="datatable-header d-flex justify-content-between p-2">
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
            <div class="me-2 position-relative" :id="props.options.id + '-column-select'">
                <button
                    @click="activeColumnsDropdown = !activeColumnsDropdown"
                    class="btn btn-outline-primary d-flex justify-content-center align-items-center"
                >
                    <p>Columns</p>
                    <font-awesome-icon :icon="['fas', 'chevron-down']" class="ms-1" />
                </button>
                <div v-show="activeColumnsDropdown" class="datatable-columns-dropdown p-2">
                    <div
                        v-for="column in props.options.columns.filter((x) => !x.isHidden)"
                        :key="column.key"
                        class="d-flex align-items-center justify-content-start"
                    >
                        <Checkbox
                            @click="toggleUserHiddenColumn(column)"
                            :checked="isUserHidden(column)"
                            class="me-2"
                        />
                        <p>{{ column.title }}</p>
                    </div>
                </div>
            </div>
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
        <table class="relation-table" :id="props.options.id">
            <thead>
                <tr>
                    <th
                        v-for="(data, key) in props.options.columns.filter((x) => !x.isHidden && !isUserHidden(x))"
                        :key="key"
                        class="datatable-table-header"
                        :style="{
                            ...data.style,
                            position: 'sticky',
                            top: '-1px',
                            left: data.isSticky ? key * 150 + 'px' : 0,
                            zIndex: data.isSticky ? 3 : 2,
                            cursor: data.isSearchable ? 'pointer' : 'default'
                        }"
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
                            style="position: relative"
                        >
                            <p
                                class="datatable-table-header-text d-inline-flex align-items-center"
                                @click="sortBy(data)"
                                :style="{
                                    cursor: data.isSearchable ? 'pointer' : 'default',
                                    textDecoration: data.column == form.orderBy ? 'underline' : 'none'
                                }"
                                style="height: 100% !important"
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
                                v-if="activeFilters && data.isSearchable"
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
                        style="position: sticky; top: -1px; z-index: 2; min-width: 100px !important; width: 10px"
                    ></th>
                </tr>
            </thead>
            <tbody v-if="api.items.length">
                <tr v-for="(item, index) in api.items" :key="index">
                    <td
                        v-for="(data, key) in props.options.columns.filter((x) => !x.isHidden && !isUserHidden(x))"
                        :key="key"
                        class="datatable-table-column"
                        :class="[
                            'datatable-col',
                            data.isSticky ? `datatable-col-sticky datatable-col-sticky-${key + 1}` : '',
                            key === props.options.columns.filter((x) => x.isSticky).length - 1
                                ? 'datatable-col-last-sticky'
                                : ''
                        ]"
                        :style="{ ...data.style, left: data.isSticky ? key * 150 + 'px' : 0 }"
                    >
                        <slot
                            v-if="usedSlots.includes(data.column)"
                            :index="index"
                            :data="item"
                            :name="data.column"
                            :value="getValue(item, data.column)"
                            :id="`${data.column}-${index}`"
                        />
                        <div v-else class="d-flex justify-content-between">
                            <p class="small overflow-hidden text-truncate" :title="getValue(item, data.column)">
                                {{ getValue(item, data.column) }}
                            </p>
                            <div
                                v-if="isArrayValue(item, data.column).items.length"
                                class="badge badge-primary position-relative tooltip-indicator cursor-pointer"
                                @click="(ev) => sendTooltipValues(ev, isArrayValue(item, data.column))"
                            >
                                +{{ isArrayValue(item, data.column).items.length }}
                            </div>
                        </div>
                    </td>
                    <td
                        v-if="props.options.allow.update || props.options.allow.link || props.options.allow.destroy"
                        :class="['datatable-col', 'p-0']"
                        style="width: 10px"
                    >
                        <div
                            class="d-flex justify-content-end align-items-center datatable-actions-menu"
                            style="position: relative"
                        >
                            <button
                                type="button"
                                class="btn p-2 border-0 datatable-actions-trigger"
                                @click.stop="activeMenuIndex = activeMenuIndex === index ? null : index"
                            >
                                <font-awesome-icon :icon="['fas', 'ellipsis-vertical']" />
                            </button>
                            <div v-if="activeMenuIndex === index" class="datatable-actions-dropdown">
                                <template v-if="props.options.allow.update">
                                    <Link
                                        :id="'datatable-btn-' + index + '-edit'"
                                        :href="props.options.route + '/' + item.id + '/edit'"
                                        class="datatable-actions-item"
                                    >
                                        <font-awesome-icon :icon="['fas', 'pen-to-square']" class="me-2" />
                                        Edit
                                    </Link>
                                </template>
                                <template v-if="props.options.allow.link">
                                    <Link
                                        :id="'datatable-btn-' + index + '-show'"
                                        :href="props.options.route + '/' + item.id"
                                        class="datatable-actions-item"
                                    >
                                        <font-awesome-icon :icon="['fas', 'eye']" class="me-2" />
                                        View
                                    </Link>
                                </template>
                                <template v-if="props.options.allow.destroy">
                                    <button
                                        type="button"
                                        :id="'datatable-btn-' + index + '-delete'"
                                        class="datatable-actions-item datatable-actions-item-danger"
                                        @click="
                                            () => {
                                                deleteRecord(item.id);
                                                activeMenuIndex = null;
                                            }
                                        "
                                    >
                                        <font-awesome-icon :icon="['fas', 'trash']" class="me-2" />
                                        Delete
                                    </button>
                                </template>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
            <tbody v-else-if="api.isLoading">
                <tr>
                    <td :colspan="props.options.columns.filter((x) => !x.isHidden).length + 1" class="text-center">
                        <font-awesome-icon :icon="['fas', 'spinner']" spin size="2x" class="mt-3" />
                    </td>
                </tr>
            </tbody>
            <tbody v-if="!api.items.length && !api.isLoading">
                <tr>
                    <td :colspan="props.options.columns.filter((x) => !x.isHidden).length + 1">
                        <p class="w-full text-center py-2">No records found</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div
        :id="props.options.id + '-tooltip'"
        v-show="showTooltip"
        class="datatable-tooltip"
        :style="{ top: tooltipPosition.top + 'px', left: 'calc(' + tooltipPosition.left + 'px - 125px) ' }"
    >
        <p
            class="small overflow-hidden text-truncate"
            v-for="item in tooltipValues.items"
            :title="getValue(item, tooltipValues.keys)"
        >
            {{ getValue(item, tooltipValues.keys) }}
        </p>
    </div>
    <Paginator
        v-model:first="api.meta.first"
        :rows="api.meta.limit"
        :totalRecords="api.meta.total"
        :rowsPerPageOptions="[10, 20, 50]"
        @page="api.handlePaginator"
        :pt="{
            root: {
                style: 'background: none'
            }
        }"
    >
    </Paginator>
</template>
<style scoped>
.relation-table {
    width: 100%;
    table-layout: fixed;
}
.datatable-header {
    background-color: white;
    border-radius: 4px 4px 0 0;
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
    text-overflow: ellipsis;
    white-space: nowrap;
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
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.datatable-col {
    box-sizing: border-box;
    padding: 0.5rem;
    vertical-align: top;
    border-bottom: 1px solid #dee2e6;
    width: 150px;
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
    z-index: 2;
}
.datatable-col-sticky-3 {
    position: sticky;
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
.datatable-columns-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background-color: white;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 0.5rem;
    z-index: 10;
    width: 250px;
    font-size: 14px;
}

.datatable-actions-trigger {
    color: #6c757d;
    background: transparent;
}
.datatable-actions-trigger:hover {
    background-color: #f1f3f5;
    color: #343a40;
}
.datatable-actions-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background-color: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    z-index: 100;
    min-width: 160px;
    padding: 4px 0;
}
.datatable-actions-item {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 8px 16px;
    font-size: 14px;
    color: #343a40;
    background: transparent;
    border: none;
    text-align: left;
    text-decoration: none;
    cursor: pointer;
    transition: background-color 0.15s;
}
.datatable-actions-item:hover {
    background-color: #f8f9fa;
    color: #343a40;
}
.datatable-actions-item-danger {
    color: #dc3545;
}
.datatable-actions-item-danger:hover {
    background-color: #fff5f5;
    color: #dc3545;
}

.datatable-tooltip {
    position: fixed;
    width: 200px;
    background-color: #101828;
    z-index: 1000;
    padding: 0.5rem;
    border-radius: 4px;
    color: white;
    font-size: 12px;
}
</style>
