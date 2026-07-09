<script setup>
import _, { chain } from 'lodash';
import { Link } from '@inertiajs/vue3';

import { IconField, InputIcon, InputText } from 'primevue';

import { computed, nextTick, reactive, ref, useSlots, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { exportData } from '@/Composables/export.js';
import { useDatatableColumns } from '@composables/useDatatableColumns.js';
import { useDatatableTooltip } from '@composables/useDatatableTooltip.js';
import { DatatableOptions } from './DatatableOptions';
import { Api } from '../../Composables/useApi';
import { v4 as uuidv4 } from 'uuid';

const props = defineProps({
    id: {
        required: false,
        default: () => {
            return `${uuidv4()}`;
        }
    },
    options: DatatableOptions,
    pollInterval: {
        type: Number,
        default: null
    },
    params: {
        type: Object,
        default: () => ({})
    }
});

const {
    hiddenColumns: userHiddenColumns,
    isUserHidden,
    toggleHidden: toggleUserHiddenColumn,
    colWidths: columnWidths,
    hasCustomWidths,
    getColWidth,
    applyMeasuredWidths,
    startResize,
    columnOrder,
    dragState,
    onDragStart,
    onDragOver,
    onDrop,
    visibleColumns,
    stickyCount,
    stickyLeft
} = useDatatableColumns(props.options.id, props.options.columns);
const {
    showTooltip,
    tooltipValues,
    tooltipPosition,
    openTooltip: sendTooltipValues,
    hideTooltip
} = useDatatableTooltip();

const slots = useSlots();
const usedSlots = Object.keys(slots);

const tableContainer = ref(null);
const isScrolled = ref(false);
const activeColumnsDropdown = ref(false);

// First-load auto-sizing: while true, columns render at natural content width (table-layout:
// auto, no forced width) instead of the resizable fixed-width mode, just long enough to measure
// each header's rendered width and bake it into colWidths - see maybeAutoSizeColumns() below.
const isAutoSizing = ref(false);
const thRefs = ref({});
const setThRef = (column, el) => {
    if (el) {
        thRefs.value[column] = el;
    }
};

const onScroll = () => {
    isScrolled.value = tableContainer.value?.scrollLeft > 0;
    hideTooltip();
};

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
    props.options.columns.forEach((el) => {
        const custom = props.options.columnFilters[el.column];
        form.filters[el.column] = { column: el.column, value: null, operator: custom?.operator ?? 'contains' };
    });
};
setFilters();

const activeFilterCount = computed(
    () => Object.values(form.filters).filter((x) => x.value != null && x.value !== '').length
);

let pollTimeoutId = null;

const getData = async () => {
    clearTimeout(pollTimeoutId);
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
    api.setParams(props.params);
    await api.getData();
    emit('update:meta', api.meta);

    if (props.pollInterval && !api.meta.is_request_completed) {
        pollTimeoutId = setTimeout(getData, props.pollInterval);
    }
};

let autoSizeAttempted = false;

const maybeAutoSizeColumns = async () => {
    if (autoSizeAttempted || hasCustomWidths.value || !api.items.length) {
        return;
    }
    autoSizeAttempted = true;

    isAutoSizing.value = true;
    await nextTick();
    await new Promise((resolve) => requestAnimationFrame(resolve));

    const measurements = {};
    props.options.columns.forEach((col) => {
        const el = thRefs.value[col.column];
        if (el) {
            measurements[col.column] = el.offsetWidth;
        }
    });

    applyMeasuredWidths(measurements);
    isAutoSizing.value = false;
};

watch(
    () => api.items.length,
    () => maybeAutoSizeColumns()
);

const sortBy = (data) => {
    if (!data.isSortable) {
        return;
    }
    let changeSort = form.order == 'asc' ? 'desc' : 'asc';
    form.order = form.orderBy === data.column ? changeSort : form.order;
    form.orderBy = data.column;
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

const exportDisabled = ref(false);

const getExportData = async () => {
    if (exportDisabled.value) return;
    exportDisabled.value = true;

    const exportColumns = columnOrder.value.filter(
        (col) => !col.isHidden && !isUserHidden(col) && col.column !== 'actions'
    );

    let filters = {
        filters: JSON.stringify([
            ...Object.values(form.filters).filter((x) => x.value != null),
            ...props.options.query
        ]),
        search: form.search,
        columns: exportColumns.map((x) => x.column).join(','),
        column_titles: exportColumns.map((x) => x.title).join(',')
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
    () => [form.filters, form.search, props.options.query, props.params],
    () => {
        api.isLoading = true;
        debouncedSearch();
    },
    { deep: true }
);

defineExpose({ getData });

onMounted(() => {
    debouncedSearch();
    window.addEventListener(
        'scroll',
        () => {
            hideTooltip();
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
            hideTooltip();
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

onUnmounted(() => {
    clearTimeout(pollTimeoutId);
});
</script>
<template>
    <div class="datatable-header d-flex justify-content-between p-2">
        <div>
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
        </div>
        <div class="d-flex">
            <div class="me-2">
                <button
                    type="button"
                    class="btn d-flex justify-content-center align-items-center"
                    :class="[activeFilters ? 'btn-primary' : 'btn-outline-primary']"
                    @click="() => (activeFilters = !activeFilters)"
                >
                    <font-awesome-icon :icon="['fas', 'filter']" class="me-1" />
                    <p class="small">Filter</p>
                    <span v-if="activeFilterCount" class="datatable-filter-count">{{ activeFilterCount }}</span>
                </button>
            </div>
            <div class="me-2 position-relative" :id="props.options.id + '-column-select'">
                <button
                    @click="activeColumnsDropdown = !activeColumnsDropdown"
                    class="btn btn-outline-primary d-flex justify-content-center align-items-center"
                >
                    <p class="small">Columns</p>
                    <font-awesome-icon :icon="['fas', 'chevron-down']" class="ms-1" />
                </button>
                <div v-show="activeColumnsDropdown" class="datatable-columns-dropdown p-2">
                    <div
                        v-for="column in columnOrder.filter((x) => !x.isHidden)"
                        :key="column.key"
                        class="d-flex align-items-center justify-content-start"
                    >
                        <Checkbox
                            @click="toggleUserHiddenColumn(column)"
                            :checked="!isUserHidden(column)"
                            class="me-2"
                        />
                        <p class="datatable-columns-dropdown-label">{{ column.title }}</p>
                    </div>
                </div>
            </div>
            <div class="me-2">
                <button
                    v-if="props.options.allow.export"
                    :disabled="!api.items.length || exportDisabled"
                    :severity="'secondary'"
                    class="btn btn-outline-primary d-flex justify-content-center align-items-center"
                    @click="getExportData()"
                    type="button"
                >
                    <font-awesome-icon :icon="['fas', 'file-export']" class="me-1" />
                    <p class="small">Export</p>
                </button>
            </div>
        </div>
    </div>

    <div class="datatable-body-wrapper">
        <div ref="tableContainer" class="datatable-content" :class="{ 'is-scrolled': isScrolled }" @scroll="onScroll">
            <table class="relation-table" :class="{ 'auto-layout': isAutoSizing }" :id="props.options.id">
                <thead>
                    <tr>
                        <th
                            v-for="(data, key) in visibleColumns"
                            :key="data.column"
                            :ref="(el) => setThRef(data.column, el)"
                            class="datatable-table-header"
                            :draggable="!data.isSticky"
                            :style="{
                                position: 'sticky',
                                top: '-1px',
                                left: data.isSticky ? stickyLeft(data, key) + 'px' : 0,
                                zIndex: data.isSticky ? 3 : 2,
                                cursor: data.isSticky ? 'default' : 'grab',
                                opacity: dragState.fromIndex === key ? 0.5 : 1,
                                width: isAutoSizing ? undefined : getColWidth(data) + 'px',
                                ...data.style
                            }"
                            :class="[
                                'datatable-col',
                                data.isSticky ? `datatable-col-sticky datatable-col-sticky-${key + 1}` : '',
                                key === stickyCount - 1 ? 'datatable-col-last-sticky' : ''
                            ]"
                            @dragstart="onDragStart(key)"
                            @dragover="onDragOver($event, key)"
                            @drop="onDrop"
                            @dragend="onDrop"
                        >
                            <div v-if="data.column != 'actions'" class="d-flex align-items-center w-100 pe-3">
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
                                            <font-awesome-icon
                                                v-if="form.order == 'desc'"
                                                :icon="['fas', 'sort-down']"
                                            />
                                            <font-awesome-icon v-else :icon="['fas', 'sort-up']" />
                                        </template>
                                    </span>
                                </p>
                            </div>
                            <div
                                class="col-resize-handle"
                                draggable="false"
                                @mousedown="startResize($event, data)"
                                @dragstart.prevent
                            ></div>
                        </th>
                        <th
                            v-if="props.options.allow.update || props.options.allow.link || props.options.allow.destroy"
                            class="datatable-table-header datatable-col-actions-sticky"
                            :class="['datatable-col', 'p-0']"
                            style="
                                position: sticky;
                                top: -1px;
                                right: 0;
                                z-index: 3;
                                min-width: 100px !important;
                                width: 35px;
                            "
                        ></th>
                    </tr>
                </thead>
                <tbody v-if="api.items.length">
                    <tr v-for="(item, index) in api.items" :key="index">
                        <td
                            v-for="(data, key) in visibleColumns"
                            :key="data.column"
                            class="datatable-table-column"
                            :class="[
                                'datatable-col',
                                data.isSticky ? `datatable-col-sticky datatable-col-sticky-${key + 1}` : '',
                                key === stickyCount - 1 ? 'datatable-col-last-sticky' : ''
                            ]"
                            :style="{
                                left: data.isSticky ? stickyLeft(data, key) + 'px' : 0,
                                width: isAutoSizing ? undefined : getColWidth(data) + 'px',
                                ...data.style
                            }"
                        >
                            <slot
                                v-if="usedSlots.includes(data.column)"
                                :index="index"
                                :data="item"
                                :name="data.column"
                                :value="getValue(item, data.column)"
                                :id="`${data.column}-${index}`"
                            />
                            <template v-else>
                                <div
                                    class="d-flex justify-content-between cursor-pointer position-relative tooltip-indicator"
                                    v-if="isArrayValue(item, data.column).items.length"
                                    @click="(ev) => sendTooltipValues(ev, isArrayValue(item, data.column))"
                                >
                                    <p class="small overflow-hidden text-truncate" :title="getValue(item, data.column)">
                                        {{ getValue(item, data.column) }}
                                    </p>
                                    <div class="badge badge-primary">
                                        +{{ isArrayValue(item, data.column).items.length }}
                                    </div>
                                </div>
                                <p
                                    v-else
                                    class="small overflow-hidden text-truncate"
                                    :title="getValue(item, data.column)"
                                >
                                    {{ getValue(item, data.column) }}
                                </p>
                            </template>
                        </td>
                        <td
                            v-if="props.options.allow.update || props.options.allow.link || props.options.allow.destroy"
                            :class="['datatable-col', 'p-0', 'datatable-col-actions-sticky']"
                            :style="{
                                width: '35px',
                                position: 'sticky',
                                right: 0,
                                zIndex: activeMenuIndex === index ? 200 : 1
                            }"
                        >
                            <div
                                class="d-flex justify-content-end align-items-center datatable-actions-menu"
                                style="position: relative"
                            >
                                <button
                                    type="button"
                                    class="btn py-2 px-3 border-0 datatable-actions-trigger"
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
                        <td :colspan="visibleColumns.length + 1" class="text-center">
                            <font-awesome-icon :icon="['fas', 'spinner']" spin size="2x" class="mt-3" />
                        </td>
                    </tr>
                </tbody>
                <tbody v-if="!api.items.length && !api.isLoading">
                    <tr>
                        <td :colspan="visibleColumns.length + 1">
                            <p class="w-full text-center py-2">No records found</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <DatatableFilterPanel
            v-model="activeFilters"
            :id="props.options.id"
            :columns="props.options.columns"
            :filters="form.filters"
            :column-filters="props.options.columnFilters"
            :facets="api.meta.filters"
        />
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
    <div class="datatable-footer">
        <div class="datatable-footer-left">
            <span class="datatable-footer-label">Items per page</span>
            <div class="datatable-limit-select">
                <select
                    :value="api.meta.limit"
                    @change="(e) => api.handlePaginator({ rows: +e.target.value, page: 0 })"
                >
                    <option v-for="n in [10, 20, 50]" :key="n" :value="n">{{ n }}</option>
                </select>
                <font-awesome-icon :icon="['fas', 'chevron-down']" class="datatable-limit-chevron" />
            </div>
            <span class="datatable-footer-divider"></span>
            <span class="datatable-footer-label">
                {{ (api.meta.page - 1) * api.meta.limit + 1 }}–{{
                    Math.min(api.meta.page * api.meta.limit, api.meta.total)
                }}
                <span class="datatable-footer-total">of {{ api.meta.total }}</span>
            </span>
        </div>

        <div class="datatable-footer-right">
            <button
                class="datatable-page-btn datatable-page-btn-arrow"
                :disabled="api.meta.page <= 1"
                @click="() => api.handlePaginator({ rows: api.meta.limit, page: api.meta.page - 2 })"
            >
                <font-awesome-icon :icon="['fas', 'chevron-left']" />
            </button>

            <template v-for="link in api.meta.links.slice(1, -1)" :key="link.label">
                <span v-if="link.label === '...'" class="datatable-page-ellipsis">…</span>
                <button
                    v-else
                    class="datatable-page-btn"
                    :class="{ 'datatable-page-btn-active': link.active }"
                    :disabled="!link.url"
                    @click="() => api.handlePaginator({ rows: api.meta.limit, page: +link.label - 1 })"
                >
                    {{ link.label }}
                </button>
            </template>

            <button
                class="datatable-page-btn datatable-page-btn-arrow"
                :disabled="api.meta.page >= api.meta.last_page"
                @click="() => api.handlePaginator({ rows: api.meta.limit, page: api.meta.page })"
            >
                <font-awesome-icon :icon="['fas', 'chevron-right']" />
            </button>
        </div>
    </div>
</template>
<style scoped>
.relation-table {
    width: 100%;
    table-layout: fixed;
}

.relation-table.auto-layout {
    table-layout: auto;
}

.datatable-header {
    background-color: white;
    border-radius: 4px 4px 0 0;
}

.datatable-filter-count {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 16px;
    height: 16px;
    margin-left: 6px;
    padding: 0 4px;
    border-radius: 999px;
    background-color: var(--color-primary, #6366f1);
    border: 1px solid white;
    color: white;
    font-size: 10px;
    font-weight: 600;
}

.datatable-body-wrapper {
    position: relative;
}

.datatable-content {
    background-color: white;
    min-height: 55vh;
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
}

.datatable-col-sticky {
    vertical-align: top;
}

.col-resize-handle {
    position: absolute;
    top: 0;
    right: 0;
    width: 5px;
    height: 100%;
    cursor: col-resize;
    z-index: 10;
    user-select: none;
}

.col-resize-handle:hover {
    background-color: var(--color-primary, #6366f1);
    opacity: 0.4;
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
    max-height: 240px;
    overflow-y: auto;
}

.datatable-columns-dropdown-label {
    font-size: 12px;
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

.datatable-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    border-top: 1px solid #dee2e6;
    border-radius: 0 0 4px 4px;
    font-size: 13px;
}

.datatable-footer-left {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6c757d;
}

.datatable-footer-label {
    font-size: 13px;
    color: #6c757d;
}

.datatable-limit-select {
    position: relative;
    display: inline-flex;
    align-items: center;
}

.datatable-limit-select select {
    appearance: none;
    -webkit-appearance: none;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 3px 28px 3px 10px;
    font-size: 13px;
    color: #343a40;
    background: white;
    cursor: pointer;
    outline: none;
    transition: border-color 0.15s;
}

.datatable-limit-select select:hover,
.datatable-limit-select select:focus {
    border-color: var(--color-primary);
}

.datatable-limit-chevron {
    position: absolute;
    right: 8px;
    font-size: 10px;
    color: #6c757d;
    pointer-events: none;
}

.datatable-footer-divider {
    width: 1px;
    height: 16px;
    background-color: #dee2e6;
}

.datatable-footer-total {
    color: #adb5bd;
}

.datatable-footer-right {
    display: flex;
    align-items: center;
    gap: 4px;
}

.datatable-page-btn {
    min-width: 30px;
    height: 30px;
    padding: 0 6px;
    border: 0;
    border-radius: 4px;
    background: transparent;
    color: #343a40;
    font-size: 13px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.15s;
}

.datatable-page-btn:hover:not(:disabled) {
    background-color: var(--color-purple-light);
    border-color: var(--color-primary);
    color: var(--color-primary);
}

.datatable-page-btn:disabled {
    opacity: 0.4;
    cursor: default;
}

.datatable-page-btn-active {
    background-color: var(--color-primary);
    border-color: var(--color-primary);
    color: white;
}

.datatable-page-btn-active:hover:not(:disabled) {
    background-color: var(--color-purple-dark);
    border-color: var(--color-purple-dark);
}

.datatable-page-btn-arrow {
    border: none;
    background: transparent;
}

.datatable-page-btn-arrow:hover:not(:disabled) {
    background-color: var(--color-purple-light);
    border: none;
}

.datatable-page-ellipsis {
    padding: 0 4px;
    color: #6c757d;
    font-size: 13px;
    line-height: 30px;
}

.datatable-col-actions-sticky {
    background-color: white;
    box-shadow: -4px 0 8px -4px rgba(0, 0, 0, 0.12);
}

.datatable-table-header.datatable-col-actions-sticky {
    background-color: #e8e8e8;
}
</style>
