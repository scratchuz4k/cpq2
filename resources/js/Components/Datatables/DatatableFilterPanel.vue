<template>
    <!-- Side drawer: teleported to <body> so it spans the full viewport height regardless of the table. -->
    <Teleport to="body">
        <transition name="filter-drawer-fade">
            <div v-if="modelValue" class="datatable-filter-backdrop" @click="close"></div>
        </transition>
        <transition name="filter-drawer-slide">
            <div v-if="modelValue" class="datatable-filter-panel" role="dialog" aria-label="Filters">
                <div class="filter-panel-header">
                    <p class="filter-panel-title">Filters</p>
                    <div class="d-flex align-items-center">
                        <div class="position-relative" :id="id + '-filter-picker'">
                            <button
                                type="button"
                                class="filter-panel-icon-btn"
                                title="Add filter"
                                @click="filterPickerOpen = !filterPickerOpen"
                            >
                                <font-awesome-icon :icon="['fas', 'plus']" />
                            </button>
                            <div v-if="filterPickerOpen" class="filter-picker-dropdown">
                                <button
                                    v-for="column in availableFilterColumns"
                                    :key="column.column"
                                    type="button"
                                    class="filter-picker-item"
                                    @click="addFilterColumn(column.column)"
                                >
                                    {{ column.title }}
                                </button>
                                <p v-if="!availableFilterColumns.length" class="filter-picker-empty small mb-0">
                                    All filterable columns are added
                                </p>
                            </div>
                        </div>
                        <button
                            v-if="addedColumnConfigs.length || $slots.default"
                            type="button"
                            class="filter-panel-icon-btn"
                            title="Clear all"
                            @click="clearAll"
                        >
                            <font-awesome-icon :icon="['fas', 'eraser']" />
                        </button>
                        <button type="button" class="filter-panel-icon-btn" title="Close" @click="close">
                            <font-awesome-icon :icon="['fas', 'x']" />
                        </button>
                    </div>
                </div>
                <div class="filter-panel-body">
                    <!-- Page-specific filters that don't map onto a single column (e.g. SPA entity filters). -->
                    <div v-if="$slots.default" class="filter-panel-row">
                        <slot />
                    </div>
                    <div v-for="column in addedColumnConfigs" :key="column.column" class="filter-panel-row">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="filter-panel-label">{{ column.title }}</label>
                            <button
                                type="button"
                                class="filter-chip-remove ms-1"
                                @click="removeFilterColumn(column.column)"
                            >
                                <font-awesome-icon :icon="['fas', 'x']" />
                            </button>
                        </div>
                        <div>
                            <select
                                v-if="['select', 'checkbox'].includes(filterType(column.column))"
                                v-model="filters[column.column].value"
                                :ref="(el) => setInputRef(column.column, el)"
                                class="form-select form-select-sm w-100"
                            >
                                <option :value="null">All</option>
                                <option
                                    v-for="opt in filterOptions(column.column)"
                                    :key="optionValue(opt)"
                                    :value="optionValue(opt)"
                                >
                                    {{ optionLabel(opt) }}
                                </option>
                            </select>
                            <div v-else-if="filterType(column.column) === 'multiselect'" class="filter-panel-checklist">
                                <label
                                    v-for="opt in filterOptions(column.column)"
                                    :key="optionValue(opt)"
                                    class="filter-panel-checkbox"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="(filters[column.column].value ?? []).includes(optionValue(opt))"
                                        @change="toggleMultiValue(column.column, optionValue(opt))"
                                    />
                                    <span class="small">{{ optionLabel(opt) }}</span>
                                </label>
                                <p v-if="!filterOptions(column.column).length" class="filter-panel-empty small mb-0">
                                    No options available
                                </p>
                            </div>
                            <input
                                v-else
                                v-model="filters[column.column].value"
                                :ref="(el) => setInputRef(column.column, el)"
                                class="form-control form-control-sm w-100"
                                :placeholder="'Filter ' + column.title"
                            />
                        </div>
                    </div>
                    <p v-if="!addedColumnConfigs.length && !$slots.default" class="filter-panel-empty small">
                        No filters added yet
                    </p>
                </div>
            </div>
        </transition>
    </Teleport>
</template>
<script setup>
import { computed, ref, nextTick, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false
    },
    id: {
        type: String,
        required: true
    },
    columns: {
        type: Array,
        required: true
    },
    // The parent's `form.filters` object (keyed by column name), mutated in place -
    // same pattern already used for DatatableOptions elsewhere in this component family.
    filters: {
        type: Object,
        required: true
    },
    // Per-column widget config from DatatableOptions.addColumnFilter(), keyed by column name.
    columnFilters: {
        type: Object,
        default: () => ({})
    },
    // Facet counts from the latest response (api.meta.filters), used as live 'select'/'multiselect'
    // options when a column's addColumnFilter() call didn't provide a static options list.
    facets: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['update:modelValue', 'clear']);

const filterPickerOpen = ref(false);

const close = () => emit('update:modelValue', false);

const handleKeydown = (ev) => {
    if (ev.key === 'Escape' && props.modelValue) {
        close();
    }
};
// Which columns currently show a row in the panel - independent of whether they have a
// value yet, so a freshly added filter is visible (and focusable) before anything is typed.
const addedColumns = ref([]);
const inputRefs = ref({});

// Hidden columns still feed the keyword search, but have no visible column to filter by.
const searchableColumns = computed(() =>
    props.columns.filter((x) => x.isSearchable && !x.isHidden && x.column !== 'actions')
);

const addedColumnConfigs = computed(() =>
    addedColumns.value.map((column) => searchableColumns.value.find((x) => x.column === column)).filter(Boolean)
);

const availableFilterColumns = computed(() =>
    searchableColumns.value.filter((x) => !addedColumns.value.includes(x.column))
);

const setInputRef = (column, el) => {
    if (el) {
        inputRefs.value[column] = el;
    }
};

const filterType = (column) => props.columnFilters[column]?.type ?? 'text';

// Plain values and provider-image-map-style {label, value} entries are both used elsewhere
// in this codebase (see QuotesConnectivityTable's filterList), so support either shape here too.
const optionValue = (opt) => (opt != null && typeof opt === 'object' ? opt.value : opt);
const optionLabel = (opt) => (opt != null && typeof opt === 'object' ? opt.label : opt);

const filterOptions = (column) => {
    const staticOptions = props.columnFilters[column]?.options ?? [];
    if (staticOptions.length) {
        return staticOptions;
    }

    // 1/0 rather than true/false: booleans reach the api as the strings 'true'/'false', which
    // MySQL coerces to 0 when compared against a tinyint column, so both would match "false".
    if (filterType(column) === 'checkbox') {
        return [
            { label: 'Yes', value: 1 },
            { label: 'No', value: 0 }
        ];
    }

    const facetCounts = props.facets[column] ?? {};
    return Object.keys(facetCounts)
        .filter((key) => key !== 'null')
        .map((key) => ({ value: key, label: facetCounts[key] ? `${key} (${facetCounts[key]})` : key }));
};

const toggleMultiValue = (column, value) => {
    const current = props.filters[column].value ?? [];
    const next = current.includes(value) ? current.filter((x) => x !== value) : [...current, value];
    props.filters[column].value = next.length ? next : null;
};

const addFilterColumn = async (column) => {
    addedColumns.value.push(column);
    filterPickerOpen.value = false;
    await nextTick();
    inputRefs.value[column]?.focus();
};

const removeFilterColumn = (column) => {
    props.filters[column].value = null;
    addedColumns.value = addedColumns.value.filter((x) => x !== column);
    delete inputRefs.value[column];
};

const clearAll = () => {
    addedColumns.value.forEach((column) => {
        props.filters[column].value = null;
    });
    addedColumns.value = [];
    inputRefs.value = {};
    emit('clear');
};

const handleDocumentClick = (ev) => {
    const picker = document.getElementById(props.id + '-filter-picker');
    if (picker && !picker.contains(ev.target)) {
        filterPickerOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleDocumentClick);
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('click', handleDocumentClick);
    document.removeEventListener('keydown', handleKeydown);
});
</script>
<style scoped>
.datatable-filter-backdrop {
    position: fixed;
    inset: 0;
    background-color: rgba(16, 24, 40, 0.3);
    z-index: 1040;
}

.datatable-filter-panel {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 420px;
    max-width: 100vw;
    background-color: white;
    box-shadow: -8px 0 24px rgba(0, 0, 0, 0.15);
    z-index: 1045;
    display: flex;
    flex-direction: column;
}

.filter-drawer-slide-enter-active,
.filter-drawer-slide-leave-active {
    transition: transform 0.2s ease;
}

.filter-drawer-slide-enter-from,
.filter-drawer-slide-leave-to {
    transform: translateX(100%);
}

.filter-drawer-fade-enter-active,
.filter-drawer-fade-leave-active {
    transition: opacity 0.2s ease;
}

.filter-drawer-fade-enter-from,
.filter-drawer-fade-leave-to {
    opacity: 0;
}

.filter-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid #dee2e6;
}

.filter-panel-title {
    font-size: 14px;
    font-weight: 500;
    margin: 0;
}

.filter-panel-icon-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    margin-left: 4px;
    padding: 0;
    border: none;
    border-radius: 4px;
    background: transparent;
    color: #6c757d;
    font-size: 12px;
}

.filter-panel-icon-btn:hover {
    background-color: #f1f3f5;
    color: #343a40;
}

.filter-panel-body {
    flex: 1;
    overflow-y: auto;
    padding: 12px 16px;
}

.filter-panel-row {
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #dee2e6;
}

.filter-panel-row:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.filter-panel-label {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: #646464;
}
/*
.filter-panel-input {
    flex: 1;
    height: 32px;
    padding: 0 8px;
    font-size: 13px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    outline: none;
}

.filter-panel-input:focus {
    border-color: var(--color-primary, #6366f1);
}
*/
.filter-panel-empty {
    color: #6c757d;
}

.filter-panel-checkbox {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0;
    cursor: pointer;
}

.filter-panel-checklist {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}

.filter-chip-remove {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    padding: 0;
    border: none;
    border-radius: 50%;
    background: transparent;
    color: #6c757d;
    font-size: 10px;
}

.filter-chip-remove:hover {
    background-color: #f1f3f5;
    color: #343a40;
}

.filter-picker-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 4px;
    background-color: white;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 4px;
    z-index: 10;
    min-width: 180px;
    max-height: 200px;
    overflow-y: auto;
}

.filter-picker-item {
    display: block;
    width: 100%;
    text-align: left;
    padding: 6px 10px;
    font-size: 12px;
    border: none;
    background: transparent;
    border-radius: 4px;
}

.filter-picker-item:hover {
    background-color: #f1f3f5;
}

.filter-picker-empty {
    padding: 6px 10px;
    color: #6c757d;
}
</style>
<style>
/* Unscoped: these target InputSelects passed in through the panel's slot. Their dropdown floats
   (absolute) and sizes itself to its longest option (inline `width: max-content`), so in this narrow,
   scrolling panel it spilled past the edge and covered the fields below. Here it opens in-flow at the
   input's width instead, pushing the following fields down, with long labels wrapping. */
.datatable-filter-panel .filter-panel-body {
    overflow-x: hidden;
}

.datatable-filter-panel [id^='input-select-'] > .form-control.position-absolute {
    position: static !important;
    width: 100% !important;
    white-space: normal !important;
    overflow-wrap: anywhere;
}

/* Closed dropdowns are only hidden via inline visibility/height, which would still leave their
   padding and border taking space now that they are in-flow. */
.datatable-filter-panel [id^='input-select-'] > .form-control.position-absolute[style*='visibility: hidden'] {
    display: none;
}

.datatable-filter-panel [id^='input-select-'] > .form-select > span {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
