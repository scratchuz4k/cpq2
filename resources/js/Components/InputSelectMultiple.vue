<script setup>
import _ from 'lodash';
import { IconField, InputIcon, InputText } from 'primevue';
import queryString from 'qs';
import { ref, watch, computed, reactive, onMounted } from 'vue';
import { v4 as uuidv4 } from 'uuid';

const props = defineProps({
    id: {
        required: false,
        default: () => {
            return `${uuidv4()}`;
        }
    },
    url: String,
    fields: {
        type: Array,
        default: []
    },
    modelValue: Array,
    placeholder: String,
    query: {
        type: Array,
        default: []
    },
    filter: {
        type: Boolean,
        default: true
    }
});

const meta = reactive({
    total_pages: 0,
    total: 0
});

const emit = defineEmits(['update:modelValue', 'change']);
const page = ref(1);
const options = ref([]);
const isLoading = ref(false);
const isSelectingAll = ref(false);
const showOptions = ref(false);
const inputSearch = ref(null);

const proxyValue = computed({
    get: () => props.modelValue ?? [],
    set: (value) => {
        emit('update:modelValue', value);
        emit('change', value);
    }
});

const search = _.debounce(async (clearOptions = false) => {
    if (!props.url) return;

    const columns = Array.isArray(props.fields) ? props.fields.filter(Boolean).join(',') : props.fields;
    const queryParams = {
        filters: props.query,
        page: page.value,
        search: inputSearch.value
    };

    if (columns) {
        queryParams.columns = columns;
    }

    let string = queryString.stringify(queryParams);

    isLoading.value = true;
    let res = await axios.get(`/api${props.url}?${string}`);
    if (clearOptions) {
        options.value = [];
    }
    meta.total_pages = res.data.meta?.last_page ?? res.data.last_page;
    meta.total = res.data.meta?.total ?? res.data.total ?? options.value.length;
    options.value.push(...res.data.data);
    isLoading.value = false;
}, 200);

const selectAll = async (e) => {
    e.stopPropagation();
    if (!props.url || isSelectingAll.value) return;

    const columns = Array.isArray(props.fields) ? props.fields.filter(Boolean).join(',') : props.fields;
    const queryParams = {
        filters: props.query,
        all: 1,
        search: inputSearch.value
    };

    if (columns) {
        queryParams.columns = columns;
    }

    let string = queryString.stringify(queryParams);

    isSelectingAll.value = true;
    try {
        let res = await axios.get(`/api${props.url}?${string}`);
        let allMatching = res.data.data ?? res.data;
        allMatching.forEach((option) => {
            if (!isSelected(option)) {
                proxyValue.value.push(option);
            }
        });
    } finally {
        isSelectingAll.value = false;
    }
};

onMounted(() => {
    var sidebar = document.getElementById(props.id + '-sidebar');

    sidebar.addEventListener(
        'scroll',
        () => {
            var isAtBottom = sidebar.scrollTop + sidebar.clientHeight === sidebar.scrollHeight;
            if (isAtBottom && page.value < meta.total_pages) {
                page.value++;
                search();
            }
        },
        { passive: true }
    );

    var select = document.getElementById('input-select-' + props.id);
    document.addEventListener('click', (ev) => {
        if (!select.contains(ev.target)) {
            showOptions.value = false;
        }
    });
});

const isSelected = (option) => !!proxyValue.value.find((x) => x.id === option.id);

const selectOption = (option) => {
    let index = proxyValue.value.findIndex((x) => x.id === option.id);
    if (index === -1) {
        proxyValue.value.push(option);
        return;
    }
    proxyValue.value.splice(index, 1);
};

const clear = (e) => {
    e.stopPropagation();
    proxyValue.value.splice(0, proxyValue.value.length);
};

watch(inputSearch, () => {
    page.value = 1;
    search(true);
});

watch(
    () => props.query,
    () => {
        page.value = 1;
        search(true);
    },
    { deep: true }
);

search();
</script>

<template>
    <div :id="'input-select-' + props.id" class="position-relative">
        <div
            class="trigger-select w-100 d-flex align-items-center cursor-pointer gap-2"
            role="button"
            @click="showOptions = !showOptions"
        >
            <span>{{ props.placeholder }}</span>
            <font-awesome-icon :icon="['fas', 'chevron-down']" style="font-size: 0.65rem; opacity: 0.6" />
            <span class="ms-auto d-flex align-items-center gap-2">
                <span v-if="proxyValue.length" class="badge rounded-pill text-white">
                    {{ proxyValue.length }}
                </span>
                <font-awesome-icon
                    v-if="proxyValue.length"
                    role="button"
                    style="font-size: 0.8rem"
                    :icon="['fas', 'xmark']"
                    @click.stop="clear"
                />
            </span>
        </div>
        <div
            class="form-control position-absolute mt-1 p-1"
            :style="{
                visibility: !showOptions ? 'hidden' : 'visible',
                height: !showOptions ? '0' : 'auto'
            }"
            style="z-index: 100; width: max-content; min-width: 100%; white-space: nowrap"
        >
            <div class="select-all d-flex align-items-center justify-content-between mb-1 px-1" v-if="options.length">
                <button type="button" class="select-all-action small" @click.stop="selectAll">
                    <font-awesome-icon v-if="isSelectingAll" :icon="['fa', 'spinner']" class="fa-spin me-1" />
                    Select all<template v-if="meta.total"> ({{ meta.total }})</template>
                </button>
                <button type="button" class="select-all-action small" @click.stop="clear">Clear</button>
            </div>
            <IconField v-if="props.filter" class="p-0">
                <InputIcon v-if="isLoading">
                    <font-awesome-icon :icon="['fa', 'spinner']" class="fa-spin"></font-awesome-icon>
                </InputIcon>
                <InputIcon v-else>
                    <font-awesome-icon :icon="['fas', 'search']"></font-awesome-icon>
                </InputIcon>
                <InputText
                    :id="'select-' + props.id + '-search'"
                    class="w-100"
                    size="small"
                    v-model="inputSearch"
                    placeholder="Search"
                    type="search"
                >
                </InputText>
            </IconField>

            <div :id="props.id + '-sidebar'" class="options-container mt-2" style="max-height: 300px; overflow-y: auto">
                <div class="px-2 py-1" v-if="!options.length">
                    <p class="ps-1 small">No available options</p>
                </div>
                <div
                    v-else
                    v-for="option in options"
                    class="option d-flex align-items-center gap-2"
                    :class="{ selected: isSelected(option) }"
                    @click.stop="selectOption(option)"
                    style="cursor: pointer"
                >
                    <span class="flex-shrink-0 option-checkbox">
                        <font-awesome-icon v-if="isSelected(option)" :icon="['fas', 'square-check']" />
                        <font-awesome-icon v-else :icon="['far', 'square']" />
                    </span>
                    <span class="flex-grow-1">
                        <template v-for="(field, index) in props.fields">
                            {{ index !== 0 ? ` (${_.get(option, field, '')})` : _.get(option, field, '') }}
                        </template>
                    </span>
                </div>
                <div class="m-auto" v-if="page < meta.total_pages">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.trigger-select {
    padding: 0.375rem 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background-color: #fff;
    font-size: 1rem;
    line-height: 1.5;
}

.options-container {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.option {
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
}

.option-checkbox {
    color: var(--color-purple);
}

.option-checkbox svg {
    height: 1.125rem;
}

.option:hover {
    background-color: var(--color-purple-light);
}

.select-all {
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--color-purple);
}

.select-all-action {
    background: none;
    border: none;
    padding: 0;
    font-weight: 600;
    color: var(--color-purple);
}

.select-all-action.text-muted {
    color: #6c757d;
}

.select-all-action:hover {
    text-decoration: underline;
}

.badge {
    background-color: var(--color-purple);
    min-width: 20px;
    height: 20px;
    font-size: 10px;
    line-height: 14px;
}
</style>
