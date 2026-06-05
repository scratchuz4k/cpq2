<script setup>
import { ref, computed, onMounted } from 'vue';
import { IconField, InputIcon, InputText } from 'primevue';
import { v4 as uuidv4 } from 'uuid';

const props = defineProps({
    modelValue: Array,
    stepId: {
        type: Number,
        default: null
    },
    placeholder: {
        type: String,
        default: 'Category'
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const id = `categories-tree-${uuidv4()}`;
const tree = ref([]);
const isLoading = ref(false);
const showOptions = ref(false);
const searchQuery = ref('');

const proxyValue = computed({
    get: () => props.modelValue ?? [],
    set: (value) => {
        emit('update:modelValue', value);
        emit('change', value);
    }
});

async function fetchTree() {
    isLoading.value = true;
    try {
        const params = props.stepId ? `?step_id=${props.stepId}` : '';
        const res = await axios.get(`/api/categories/tree${params}`);
        tree.value = res.data;
    } catch (e) {
        tree.value = [];
        console.error(e?.response?.data?.message ?? e);
    } finally {
        isLoading.value = false;
    }
}

function flattenWithDepth(nodes, depth = 0) {
    const result = [];
    for (const node of nodes) {
        result.push({ ...node, _depth: depth });
        if (node.children?.length) {
            result.push(...flattenWithDepth(node.children, depth + 1));
        }
    }
    return result;
}

function collectDescendants(node) {
    const result = [node];
    if (node.children?.length) {
        for (const child of node.children) {
            result.push(...collectDescendants(child));
        }
    }
    return result;
}

const flatOptions = computed(() => flattenWithDepth(tree.value));

const filteredOptions = computed(() => {
    const q = searchQuery.value?.trim().toLowerCase();
    if (!q) return flatOptions.value;
    return flatOptions.value.filter((n) => n.name.toLowerCase().includes(q));
});

const selectedIds = computed(() => new Set(proxyValue.value.map((c) => c.id)));

function isSelected(option) {
    return selectedIds.value.has(option.id);
}

function select(option) {
    const descendants = collectDescendants(option);
    const alreadySelected = descendants.every((d) => selectedIds.value.has(d.id));

    if (alreadySelected) {
        const removeIds = new Set(descendants.map((d) => d.id));
        proxyValue.value = proxyValue.value.filter((c) => !removeIds.has(c.id));
    } else {
        const existingIds = selectedIds.value;
        const toAdd = descendants.filter((d) => !existingIds.has(d.id));
        proxyValue.value = [...proxyValue.value, ...toAdd];
    }

    searchQuery.value = '';
}

function clear(e) {
    e.stopPropagation();
    proxyValue.value = [];
}

onMounted(() => {
    fetchTree();

    const wrapper = document.getElementById(id);
    document.addEventListener('click', (ev) => {
        if (wrapper && !wrapper.contains(ev.target)) {
            showOptions.value = false;
        }
    });
});
</script>

<template>
    <div :id="id" class="position-relative">
        <div class="form-select w-100 d-flex cursor-pointer" @click="showOptions = !showOptions">
            <template v-if="!proxyValue.length">
                {{ placeholder }}
            </template>
            <span v-else class="text-truncate">
                {{ proxyValue.map((c) => c.name).join(', ') }}
            </span>

            <font-awesome-icon
                v-if="proxyValue.length"
                class="my-auto ms-auto flex-shrink-0"
                role="button"
                style="font-size: 0.8rem"
                :icon="['fas', 'xmark']"
                @click="clear"
            />
        </div>

        <div
            class="form-control position-absolute mt-1 p-1"
            :style="{
                visibility: showOptions ? 'visible' : 'hidden',
                height: showOptions ? 'auto' : '0'
            }"
            style="z-index: 1000; width: 100%; max-width: 300px"
        >
            <IconField class="p-0">
                <InputIcon v-if="isLoading">
                    <font-awesome-icon :icon="['fa', 'spinner']" class="fa-spin" />
                </InputIcon>
                <InputIcon v-else>
                    <font-awesome-icon :icon="['fas', 'search']" />
                </InputIcon>
                <InputText class="w-100" v-model="searchQuery" placeholder="Search" type="search" />
            </IconField>

            <div class="options-container mt-2" style="max-height: 300px; overflow-y: auto">
                <div class="p-2" v-if="!filteredOptions.length">
                    <p class="ps-1">No available options</p>
                </div>
                <div
                    v-else
                    v-for="option in filteredOptions"
                    :key="option.id"
                    class="option"
                    :class="{ selected: isSelected(option) }"
                    :style="{ paddingLeft: `${0.75 + option._depth * 1}rem` }"
                    @click="select(option)"
                    style="cursor: pointer"
                >
                    <span v-if="option._depth > 0" class="me-1" style="opacity: 0.4">↳</span>
                    {{ option.name }}
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
