<script setup>
import _ from 'lodash';
import { IconField, InputIcon, InputText } from 'primevue';
import { ref, computed, onMounted } from 'vue';
import { v4 as uuidv4 } from 'uuid';

const props = defineProps({
    id: {
        required: false,
        default: () => {
            return `${uuidv4()}`;
        }
    },
    options: {
        type: Array,
        default: () => []
    },
    optionLabel: {
        type: String,
        default: null
    },
    optionValue: {
        type: String,
        default: null
    },
    modelValue: {
        type: Array,
        default: () => []
    },
    placeholder: String,
    filter: {
        type: Boolean,
        default: true
    },
    filterPlaceholder: {
        type: String,
        default: 'Search'
    }
});

const emit = defineEmits(['update:modelValue', 'change']);
const showOptions = ref(false);
const inputSearch = ref(null);

const proxyValue = computed({
    get: () => props.modelValue ?? [],
    set: (value) => {
        emit('update:modelValue', value);
        emit('change', value);
    }
});

const getLabel = (option) => (props.optionLabel ? _.get(option, props.optionLabel) : option);
const getValue = (option) => (props.optionValue ? _.get(option, props.optionValue) : option);

const filteredOptions = computed(() => {
    if (!inputSearch.value) return props.options;
    const term = inputSearch.value.toLowerCase();
    return props.options.filter((option) => String(getLabel(option)).toLowerCase().includes(term));
});

const isSelected = (option) => proxyValue.value.includes(getValue(option));

const selectOption = (option) => {
    const value = getValue(option);
    const index = proxyValue.value.findIndex((x) => x === value);
    const next = [...proxyValue.value];
    if (index === -1) {
        next.push(value);
    } else {
        next.splice(index, 1);
    }
    proxyValue.value = next;
};

const clear = (e) => {
    e.stopPropagation();
    proxyValue.value = [];
};

onMounted(() => {
    const select = document.getElementById('input-select-' + props.id);
    document.addEventListener('click', (ev) => {
        if (!select.contains(ev.target)) {
            showOptions.value = false;
        }
    });
});
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
            style="z-index: 100"
        >
            <IconField v-if="props.filter" class="p-0">
                <InputIcon>
                    <font-awesome-icon :icon="['fas', 'search']"></font-awesome-icon>
                </InputIcon>
                <InputText
                    :id="'select-' + props.id + '-search'"
                    class="w-100"
                    size="small"
                    v-model="inputSearch"
                    :placeholder="props.filterPlaceholder"
                    type="search"
                >
                </InputText>
            </IconField>
            <div class="options-container mt-2" style="max-height: 300px; overflow-y: auto">
                <div class="px-2 py-1" v-if="!filteredOptions.length">
                    <p class="ps-1 small">No available options</p>
                </div>
                <div
                    v-else
                    v-for="(option, index) in filteredOptions"
                    :key="index"
                    class="option d-flex align-items-center gap-2"
                    :class="{ selected: isSelected(option) }"
                    @click.stop="selectOption(option)"
                    style="cursor: pointer"
                >
                    <span class="flex-shrink-0 option-checkbox">
                        <font-awesome-icon v-if="isSelected(option)" :icon="['fas', 'square-check']" />
                        <font-awesome-icon v-else :icon="['far', 'square']" />
                    </span>
                    <span class="flex-grow-1">{{ getLabel(option) }}</span>
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
    font-size: 0.875rem;
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
    font-size: 0.875rem;
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

.badge {
    background-color: var(--color-purple);
    min-width: 20px;
    height: 20px;
    font-size: 10px;
    line-height: 14px;
}
</style>
