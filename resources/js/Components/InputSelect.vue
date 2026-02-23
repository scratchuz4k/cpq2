<script setup>
import _ from 'lodash';
import { IconField, InputIcon, InputText } from 'primevue';
import queryString from 'query-string';
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
    modelValue: Object,
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
    total_pages: 0
});

const emit = defineEmits(['update:modelValue', 'change']);
const page = ref(1);
const options = ref([]);
const isLoading = ref(false);
const showOptions = ref(false);
const inputSearch = ref(null);

const proxyValue = computed({
    get: () => props.modelValue,
    set: (value) => {
        emit('update:modelValue', value);
        emit('change', value);
    }
});

const search = _.debounce(async (clearOptions = false) => {
    if (!props.url) return;

    let string = queryString.stringify({
        filters: JSON.stringify(props.query),
        page: page.value,
        search: inputSearch.value
    });

    isLoading.value = true;
    try {
        let res = await axios.get(`/api${props.url}?${string}`);
        if (clearOptions) {
            options.value = [];
        }
        meta.total_pages = res.data.meta?.last_page ?? res.data.last_page;
        if (page.value > 1) {
            options.value.push(...res.data.data);
        } else {
            options.value = res.data.data;
        }
    } catch (error) {
        options.value = [];
        console.error(error.response?.data?.message ?? error.response?.data);
    } finally {
        isLoading.value = false;
    }
}, 200);

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

emitter.on('input-select-refresh' + (props.id ? ':' + props.id : ''), () => {
    search();
});
</script>

<template>
    <div :id="'input-select-' + props.id" class="position-relative">
        <div class="form-select w-100 d-flex cursor-pointer" @click="() => (showOptions = !showOptions)">
            <template v-if="!proxyValue || Object.values(proxyValue).every((value) => !value)">
                {{ props.placeholder }}
            </template>
            <span v-else>
                <template v-for="(field, index) in fields">
                    {{ index !== 0 ? ` (${proxyValue?.[field] ?? ''})` : (proxyValue?.[field] ?? '') }}
                </template>
            </span>

            <font-awesome-icon
                v-if="proxyValue"
                class="my-auto ms-auto"
                role="button"
                style="font-size: 0.8rem"
                :icon="['fas', 'xmark']"
                @click="() => (proxyValue = null)"
            />
        </div>
        <div
            class="form-control position-absolute mt-1 p-1"
            :style="{
                visibility: !showOptions ? 'hidden' : 'visible',
                height: !showOptions ? '0' : 'auto'
            }"
            style="z-index: 100"
        >
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
                    v-model="inputSearch"
                    placeholder="Search"
                    type="search"
                >
                </InputText>
            </IconField>
            <div :id="props.id + '-sidebar'" class="options-container mt-2" style="max-height: 300px; overflow-y: auto">
                <div class="p-2" v-if="!options.length">
                    <p class="ps-1">No available options</p>
                </div>
                <div
                    v-else
                    v-for="option in options"
                    class="option"
                    :class="{ selected: proxyValue === option }"
                    @click="
                        () => {
                            proxyValue = option;
                            showOptions = false;
                        }
                    "
                    style="cursor: pointer"
                >
                    <template v-for="(field, index) in props.fields">
                        {{ index !== 0 ? ` (${getValue(option, field) ?? ''})` : (getValue(option, field) ?? '') }}
                    </template>
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
.options-container {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.option {
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
}

.option.selected {
    color: white;
    background-color: var(--color-purple);
}

.option:not(.selected):hover {
    color: black;
    background-color: var(--color-purple-light);
}
</style>
