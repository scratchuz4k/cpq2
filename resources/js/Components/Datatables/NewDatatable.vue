<script setup>
import _, { chain } from 'lodash';
import { Link, usePage } from '@inertiajs/vue3';

import { IconField, InputIcon, InputText } from 'primevue';

import { computed, nextTick, reactive, ref, useSlots, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import pluralize from 'pluralize';
import { exportData } from '@/Composables/export.js';
import { can, hasRole } from '@/Composables/useAuth.js';
import FileUploadModal from '@/Components/Import/FileUploadModal.vue';
import ImportPreviewModel from '@/Components/Import/ImportPreviewModel.vue';
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
    },
    filterable: {
        type: Boolean,
        default: true
    },
    // Import types offered by the import modal (see ImportsEnum::asArrayFor). Only used when
    // options.allow.import is set.
    imports: {
        type: Array,
        default: () => []
    },
    // Active filters applied by the page itself through the `filters` slot, added to the Filter badge.
    extraFilterCount: {
        type: Number,
        default: 0
    }
});

const {
    hiddenColumns: userHiddenColumns,
    isUserHidden,
    toggleHidden: toggleUserHiddenColumn,
    colWidths: columnWidths,
    hasCustomWidths,
    getDisplayColWidth,
    applyMeasuredWidths,
    startResize,
    columnOrder,
    dragState,
    onDragStart,
    onDragOver,
    onDrop,
    isReorderLocked,
    resetLayout,
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

// table-layout: fixed only honors per-cell widths correctly if the <table>'s own rendered
// width exactly matches their sum - a CSS width of 100% makes the browser instead stretch or
// shrink every column proportionally to fit the container, silently fighting manual resizes.
// So we compute the exact total ourselves and bind it directly instead of using width: 100%.
const getEffectiveColWidth = (col) => {
    const styleWidth = col.style?.width;
    if (styleWidth != null) {
        const parsed = parseFloat(styleWidth);
        if (!Number.isNaN(parsed)) return parsed;
    }
    return getDisplayColWidth(col);
};

const slots = useSlots();
const usedSlots = Object.keys(slots);

// Pages with per-row actions that don't fit edit/view/delete (e.g. conditional on the row's status)
// supply them through the `row-actions` slot, rendered inside the same ellipsis menu.
const hasRowActions = computed(
    () =>
        !!slots['row-actions'] ||
        !!(
            props.options.allow.update ||
            props.options.allow.link ||
            props.options.allow.destroy ||
            props.options.allow.duplicate
        )
);

const nativeActionsColumnWidth = computed(() => (hasRowActions.value ? 35 : 0));

const page = usePage();
const singularRoute = pluralize.singular(props.options.route);
const isImportable = (page.props.importModels ?? []).some((imp) => imp.value === props.options.route);
const canImport =
    !!props.options.allow.import &&
    isImportable &&
    ((can('create import') && can(`import ${singularRoute}`)) || hasRole('Super Admin'));

const showImportForm = ref(false);
const showImportPreview = ref(false);
const importPreviewData = ref(null);
const importForm = ref(null);

const handleImportPreview = ({ data, form }) => {
    importPreviewData.value = data;
    importForm.value = form;
    showImportPreview.value = true;
};

const tableWidth = computed(
    () => visibleColumns.value.reduce((sum, col) => sum + getEffectiveColWidth(col), 0) + nativeActionsColumnWidth.value
);

// When the real columns don't add up to the container's width, this fills the gap with a
// blank filler column instead of leaving the table box short (which left a bare white strip
// after the last column) or stretching every column proportionally (which fights manual resizes).
const containerWidth = ref(0);
const fillerWidth = computed(() => Math.max(0, containerWidth.value - tableWidth.value));
const hasActionsDataColumn = computed(() => visibleColumns.value.some((col) => col.column === 'actions'));

const tableContainer = ref(null);
const isScrolled = ref(false);
const isScrolledToEnd = ref(true);
const activeColumnsDropdown = ref(false);
const activeSettingsDropdown = ref(false);

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

const updateScrollState = () => {
    const el = tableContainer.value;
    if (!el) return;
    isScrolled.value = el.scrollLeft > 0;
    // A 1px slack absorbs rounding from fractional widths on some browsers/zoom levels.
    isScrolledToEnd.value = el.scrollLeft + el.clientWidth >= el.scrollWidth - 1;
};

const onScroll = () => {
    updateScrollState();
    hideTooltip();
};

const emit = defineEmits(['update:meta', 'clear-filters']);
const activeFilters = ref(false);
const activeMenuIndex = ref(null);
const api = reactive(new Api(props.options.route));

const form = reactive({
    filters: {},
    order: props.options.sort.order,
    orderBy: props.options.sort.orderBy,
    search: null
});

const shouldOpenUpward = (index) => {
    return api.items.length > 8 && index >= api.items.length - 2;
};

const setFilters = () => {
    form.filters = {};
    props.options.columns.forEach((el) => {
        const custom = props.options.columnFilters[el.column];
        form.filters[el.column] = { column: el.column, value: null, operator: custom?.operator ?? 'contains' };
    });
};
setFilters();

const activeFilterCount = computed(
    () => Object.values(form.filters).filter((x) => x.value != null && x.value !== '').length + props.extraFilterCount
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
        api.addFilter(x.column, x.operator, x.value, x.function ?? 'where');
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

const resetTableLayout = () => {
    resetLayout();
    autoSizeAttempted = false;
    maybeAutoSizeColumns();
};

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

watch(tableWidth, () => nextTick(updateScrollState));

watch([activeColumnsDropdown, activeSettingsDropdown], () => {
    if ((activeColumnsDropdown.value || activeSettingsDropdown.value) && activeFilters.value) {
        activeFilters.value = false;
    }
});

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
            await axios.delete('/api/' + props.options.route + '/' + id);
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

// Pages refresh a specific table with 'datatable-refresh:<id>'; the bare event (e.g. from ToggleActive)
// refreshes every table on the page.
const refreshEvents = ['datatable-refresh', 'datatable-refresh:' + props.options.id];
refreshEvents.forEach((event) => emitter.on(event, getData));

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

let containerResizeObserver = null;

onMounted(() => {
    debouncedSearch();
    if (tableContainer.value) {
        containerResizeObserver = new ResizeObserver((entries) => {
            containerWidth.value = entries[0].contentRect.width;
        });
        containerResizeObserver.observe(tableContainer.value);
    }
    window.addEventListener('scroll', hideTooltip, { passive: true });
    document.addEventListener('click', handleDocumentClick);
});

const handleDocumentClick = (ev) => {
    const select = document.getElementById(props.options.id + '-tooltip');
    const selectColumns = document.getElementById(props.options.id + '-column-select');
    const selectSettings = document.getElementById(props.options.id + '-settings-select');

    const isTooltipClick = Array.from(document.getElementsByClassName('tooltip-indicator')).some((x) =>
        x.contains(ev.target)
    );
    if (!select?.contains(ev.target) && !isTooltipClick) {
        hideTooltip();
    }

    if (!selectColumns?.contains(ev.target)) {
        activeColumnsDropdown.value = false;
    }

    if (!selectSettings?.contains(ev.target)) {
        activeSettingsDropdown.value = false;
    }

    const isMenuClick = Array.from(document.getElementsByClassName('datatable-actions-menu')).some((x) =>
        x.contains(ev.target)
    );
    if (!isMenuClick) {
        activeMenuIndex.value = null;
    }
};

onUnmounted(() => {
    clearTimeout(pollTimeoutId);
    containerResizeObserver?.disconnect();
    window.removeEventListener('scroll', hideTooltip);
    document.removeEventListener('click', handleDocumentClick);
    refreshEvents.forEach((event) => emitter.off(event, getData));
});
</script>
<template>
    <!-- Below md: search takes the first row and the buttons, icon-only, share the second row equally. -->
    <div class="datatable-header d-flex flex-wrap justify-content-between p-2">
        <div class="datatable-header-search">
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
        <div class="datatable-header-actions d-flex">
            <div v-if="props.filterable">
                <button
                    type="button"
                    class="btn d-flex justify-content-center align-items-center"
                    :class="[activeFilters ? 'btn-primary' : 'btn-outline-primary']"
                    title="Filter"
                    aria-label="Filter"
                    @click="() => (activeFilters = !activeFilters)"
                >
                    <font-awesome-icon :icon="['fas', 'filter']" class="me-md-1" />
                    <p class="small d-none d-md-block">Filter</p>
                    <span v-if="activeFilterCount" class="datatable-filter-count">{{ activeFilterCount }}</span>
                </button>
            </div>
            <div class="datatable-dropdown-anchor" :id="props.options.id + '-column-select'">
                <button
                    type="button"
                    @click="activeColumnsDropdown = !activeColumnsDropdown"
                    class="btn btn-outline-primary d-flex justify-content-center align-items-center"
                    title="Columns"
                    aria-label="Columns"
                >
                    <font-awesome-icon :icon="['fas', 'table-columns']" class="me-md-1" />
                    <p class="small d-none d-md-block">Columns</p>
                    <font-awesome-icon :icon="['fas', 'chevron-down']" class="ms-1 d-none d-md-inline-block" />
                </button>
                <div v-show="activeColumnsDropdown" class="datatable-columns-dropdown p-2">
                    <div
                        v-for="column in columnOrder.filter(
                            (x) => !x.isHidden && x.column !== 'actions' && !x.isSticky
                        )"
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
            <div v-if="props.options.allow.export">
                <button
                    :disabled="!api.items.length || exportDisabled"
                    :severity="'secondary'"
                    class="btn btn-outline-primary d-flex justify-content-center align-items-center"
                    title="Export"
                    aria-label="Export"
                    @click="getExportData()"
                    type="button"
                >
                    <font-awesome-icon :icon="['fas', 'file-export']" class="me-md-1" />
                    <p class="small d-none d-md-block">Export</p>
                </button>
            </div>
            <div v-if="canImport">
                <button
                    type="button"
                    class="btn btn-outline-primary d-flex justify-content-center align-items-center"
                    :id="props.options.id + '-import-btn'"
                    title="Import"
                    aria-label="Import"
                    @click="showImportForm = true"
                >
                    <font-awesome-icon :icon="['fas', 'file-import']" class="me-md-1" />
                    <p class="small d-none d-md-block">Import</p>
                </button>
            </div>
            <div class="datatable-dropdown-anchor" :id="props.options.id + '-settings-select'">
                <button
                    type="button"
                    class="btn btn-outline-primary d-flex justify-content-center align-items-center datatable-settings-trigger"
                    title="Table settings"
                    aria-label="Table settings"
                    @click="activeSettingsDropdown = !activeSettingsDropdown"
                >
                    <font-awesome-icon :icon="['fas', 'gear']" />
                </button>
                <div v-show="activeSettingsDropdown" class="datatable-columns-dropdown datatable-settings-dropdown p-1">
                    <button
                        type="button"
                        class="datatable-actions-item"
                        @click="
                            () => {
                                resetTableLayout();
                                activeSettingsDropdown = false;
                            }
                        "
                    >
                        <font-awesome-icon :icon="['fas', 'rotate-left']" class="me-2" />
                        Reset layout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="datatable-body-wrapper">
        <div
            ref="tableContainer"
            class="datatable-content"
            :class="{ 'is-scrolled': isScrolled, 'is-scrolled-to-end': isScrolledToEnd }"
            @scroll="onScroll"
        >
            <table
                class="relation-table"
                :class="{ 'auto-layout': isAutoSizing }"
                :id="props.options.id"
                :style="{ width: isAutoSizing ? undefined : tableWidth + fillerWidth + 'px' }"
            >
                <thead>
                    <tr>
                        <template v-for="(data, key) in visibleColumns" :key="data.column">
                            <th
                                v-if="data.column === 'actions' && fillerWidth > 0"
                                class="datatable-col-filler-header datatable-col-filler"
                                :style="{ width: fillerWidth + 'px' }"
                            ></th>
                            <th
                                :ref="(el) => setThRef(data.column, el)"
                                class="datatable-table-header"
                                :draggable="!isReorderLocked(data)"
                                :style="{
                                    position: 'sticky',
                                    top: '-1px',
                                    left: data.isSticky ? stickyLeft(data, key) + 'px' : undefined,
                                    zIndex: data.isSticky ? 3 : 2,
                                    cursor: isReorderLocked(data) ? 'default' : 'grab',
                                    opacity: dragState.fromIndex === key ? 0.5 : 1,
                                    width: isAutoSizing ? undefined : getDisplayColWidth(data) + 'px',
                                    ...data.style
                                }"
                                :class="[
                                    'datatable-col',
                                    data.isSticky ? `datatable-col-sticky datatable-col-sticky-${key + 1}` : '',
                                    key === stickyCount - 1 ? 'datatable-col-last-sticky' : '',
                                    data.column === 'actions' ? 'datatable-col-actions-shadow' : ''
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
                                            cursor: data.isSortable ? 'pointer' : 'default',
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
                        </template>
                        <th
                            v-if="!hasActionsDataColumn && fillerWidth > 0"
                            class="datatable-col-filler-header datatable-col-filler"
                            :style="{ width: fillerWidth + 'px' }"
                        ></th>
                        <th
                            v-if="hasRowActions"
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
                        <template v-for="(data, key) in visibleColumns" :key="data.column">
                            <td
                                v-if="data.column === 'actions' && fillerWidth > 0"
                                class="datatable-col-filler-footer datatable-col-filler"
                                :style="{ width: fillerWidth + 'px' }"
                            ></td>
                            <td
                                class="datatable-table-column"
                                :class="[
                                    'datatable-col',
                                    data.isSticky ? `datatable-col-sticky datatable-col-sticky-${key + 1}` : '',
                                    key === stickyCount - 1 ? 'datatable-col-last-sticky' : '',
                                    data.column === 'actions' ? 'datatable-col-actions-shadow' : ''
                                ]"
                                :style="{
                                    left: data.isSticky ? stickyLeft(data, key) + 'px' : undefined,
                                    width: isAutoSizing ? undefined : getDisplayColWidth(data) + 'px',
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
                                        <p
                                            class="small overflow-hidden text-truncate datatable-array-value-text"
                                            :title="getValue(item, data.column)"
                                        >
                                            {{ getValue(item, data.column) }}
                                        </p>
                                        <div class="badge badge-primary flex-shrink-0">
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
                        </template>
                        <td
                            v-if="!hasActionsDataColumn && fillerWidth > 0"
                            class="datatable-col-filler-footer datatable-col-filler"
                            :style="{ width: fillerWidth + 'px' }"
                        ></td>
                        <td
                            v-if="hasRowActions"
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
                                <div
                                    v-if="activeMenuIndex === index"
                                    class="datatable-actions-dropdown"
                                    :class="{ 'datatable-actions-dropdown-up': shouldOpenUpward(index) }"
                                >
                                    <template v-if="props.options.allow.update">
                                        <Link
                                            :id="'datatable-btn-' + index + '-edit'"
                                            :href="'/' + props.options.route + '/' + item.id + '/edit'"
                                            class="datatable-actions-item"
                                        >
                                            <font-awesome-icon :icon="['fas', 'pen-to-square']" class="me-2" />
                                            Edit
                                        </Link>
                                    </template>
                                    <template v-if="props.options.allow.link">
                                        <Link
                                            :id="'datatable-btn-' + index + '-show'"
                                            :href="'/' + props.options.route + '/' + item.id"
                                            class="datatable-actions-item"
                                        >
                                            <font-awesome-icon :icon="['fas', 'eye']" class="me-2" />
                                            View
                                        </Link>
                                    </template>
                                    <template v-if="props.options.allow.duplicate">
                                        <button
                                            type="button"
                                            :id="'datatable-btn-' + index + '-duplicate'"
                                            class="datatable-actions-item"
                                            @click="
                                                () => {
                                                    props.options.allow.duplicate(item);
                                                    activeMenuIndex = null;
                                                }
                                            "
                                        >
                                            <font-awesome-icon :icon="['fas', 'copy']" class="me-2" />
                                            Duplicate
                                        </button>
                                    </template>
                                    <slot
                                        name="row-actions"
                                        :data="item"
                                        :index="index"
                                        :close="() => (activeMenuIndex = null)"
                                    />
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
            v-if="props.filterable"
            v-model="activeFilters"
            :id="props.options.id"
            :columns="props.options.columns"
            :filters="form.filters"
            :column-filters="props.options.columnFilters"
            :facets="api.meta.filters"
            @clear="emit('clear-filters')"
        >
            <template v-if="slots.filters" #default>
                <slot name="filters" />
            </template>
        </DatatableFilterPanel>
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
    <template v-if="canImport">
        <FileUploadModal
            v-model:visible="showImportForm"
            :model="props.options.route"
            :imports="props.imports"
            @preview="handleImportPreview"
        />
        <ImportPreviewModel
            v-model:visible="showImportPreview"
            :preview-data="importPreviewData"
            :import-form="importForm"
        />
    </template>
</template>
<style scoped>
.relation-table {
    table-layout: fixed;
}

.relation-table.auto-layout {
    table-layout: auto;
}

.datatable-header {
    --datatable-control-height: 34px;
    position: relative;
    row-gap: 0.25rem;
    background-color: white;
    border-radius: 4px 4px 0 0;
}

.datatable-header-search {
    min-width: 0;
    flex: 0 1 auto;
}

.datatable-dropdown-anchor {
    position: relative;
}

/* Bootstrap md breakpoint - matches the d-md-* classes that hide the button labels. */
@media (max-width: 767.98px) {
    .datatable-header-search,
    .datatable-header-actions {
        flex: 1 1 100%;
    }

    /* Equal-width icon buttons filling the row, whatever the number of actions. */
    .datatable-header-actions > div {
        flex: 1 1 0;
    }

    .datatable-header-actions .btn,
    .datatable-settings-trigger {
        width: 100%;
    }

    .datatable-header-search :deep(.p-iconfield),
    .datatable-header-search :deep(input) {
        width: 100%;
    }

    /* Icon-only buttons sit close to the screen edge, so anchor their dropdowns to the header's
       right edge instead of the button, and keep them inside the viewport. */
    .datatable-dropdown-anchor {
        position: static;
    }

    .datatable-columns-dropdown {
        right: 0.5rem;
        max-width: calc(100vw - 2rem);
    }
}

.datatable-filter-count {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 16px;
    height: 16px;
    line-height: 16px;
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
    min-height: 52vh;
    overflow: auto;
}

.datatable-table-header {
    background-color: #e8e8e8;
    border-top: 1px solid #dee2e6;
    overflow: hidden;
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

.datatable-col-filler-header {
    background-color: #e8e8e8;
}

.datatable-col-filler-footer {
    border-bottom: 1px solid #dee2e6;
}

.datatable-col-filler {
    border-top: 1px solid #dee2e6;
}

.datatable-table-column {
    background-color: white;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* In the array-value badge row, this text and the badge are flex siblings. The badge has no
   overflow set so its automatic min-width is its own content size, while this <p>'s min-width
   is 0 (from overflow: hidden) - meaning as the column narrows, ALL the squeeze lands on the
   text first and it can shrink to nothing before the badge gives up any space. This floor keeps
   a sliver of text always visible; the badge yields (and itself gets clipped) instead. */
.datatable-array-value-text {
    min-width: 20px;
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

.datatable-col-actions-shadow:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 10px;
    height: 100%;
    transition: all 0.3s ease;
    pointer-events: none;
    box-shadow: inset -10px 0 8px -8px #0003;
}

.is-scrolled-to-end .datatable-col-actions-shadow:before {
    box-shadow: none;
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

/* Every header control (search included) shares one height so the row lines up. */
.datatable-header-actions {
    gap: 0.25rem;
}

.datatable-header-actions .btn {
    height: var(--datatable-control-height);
    padding: 0 0.625rem;
}

.datatable-settings-trigger {
    width: var(--datatable-control-height);
    padding: 0 !important;
}

.datatable-header-search :deep(input) {
    height: var(--datatable-control-height);
}

.datatable-settings-dropdown {
    width: max-content;
    min-width: 160px;
    max-height: none;
    overflow: visible;
}

.datatable-settings-dropdown .datatable-actions-item {
    white-space: nowrap;
    padding: 6px 10px;
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

.datatable-actions-dropdown-up {
    top: auto;
    bottom: 100%;
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
    color: white;
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
<style>
/* Unscoped: also styles items passed in through the `row-actions` slot, which are compiled
   in the parent and so never receive this component's scoped attribute. */
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
</style>
