<script setup>
import { ref, computed, onMounted } from 'vue';
import { IconField, InputIcon, InputText } from 'primevue';
import { v4 as uuidv4 } from 'uuid';
import queryString from 'qs';

const props = defineProps({
    modelValue: Array,
    stepId: {
        type: Number,
        default: null
    },
    placeholder: {
        type: String,
        default: 'Category'
    },
    selectChildren: {
        type: Boolean,
        default: true
    },
    id: {
        type: String,
        default: null
    },
    query: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const id = props.id || `categories-tree-${uuidv4()}`;
const tree = ref([]);
const isLoading = ref(false);
const showOptions = ref(false);
const searchQuery = ref('');
const collapsedIds = ref(new Set());

const proxyValue = computed({
    get: () => props.modelValue ?? [],
    set: (value) => {
        emit('update:modelValue', value);
        emit('change', value);
    }
});

function collectParentIds(nodes) {
    const ids = new Set();
    for (const node of nodes) {
        if (node.children?.length) {
            ids.add(node.id);
            for (const id of collectParentIds(node.children)) ids.add(id);
        }
    }
    return ids;
}

async function fetchTree() {
    isLoading.value = true;
    try {
        const queryParams = {
            filters: props.query
        };

        if (props.stepId) {
            queryParams.step_id = props.stepId;
        }

        let string = queryString.stringify(queryParams);
        let res = await axios.get(`/api/categories/tree?${string}`);

        tree.value = res.data;
        collapsedIds.value = collectParentIds(tree.value);
    } catch (e) {
        tree.value = [];
        console.error(e?.response?.data?.message ?? e);
    } finally {
        isLoading.value = false;
    }
}

function flattenWithDepth(nodes, depth = 0, collapsed = null) {
    const result = [];
    for (const node of nodes) {
        result.push({ ...node, _depth: depth });
        if (node.children?.length && !collapsed?.has(node.id)) {
            result.push(...flattenWithDepth(node.children, depth + 1, collapsed));
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

function toggleCollapse(e, node) {
    e.stopPropagation();
    const next = new Set(collapsedIds.value);
    if (next.has(node.id)) next.delete(node.id);
    else next.add(node.id);
    collapsedIds.value = next;
}

const flatOptions = computed(() => flattenWithDepth(tree.value, 0, collapsedIds.value));

function collectBranchMatches(nodes, q, depth = 0) {
    const result = [];
    for (const node of nodes) {
        const childMatches = node.children?.length ? collectBranchMatches(node.children, q, depth + 1) : [];
        const selfMatches = node.name.toLowerCase().includes(q);
        if (selfMatches || childMatches.length) {
            result.push({ ...node, _depth: depth });
            result.push(...childMatches);
        }
    }
    return result;
}

const filteredOptions = computed(() => {
    const q = searchQuery.value?.trim().toLowerCase();
    if (!q) return flatOptions.value;
    return collectBranchMatches(tree.value, q);
});

const selectedIds = computed(() => new Set(proxyValue.value.map((c) => c.id)));

function isSelected(option) {
    return selectedIds.value.has(option.id);
}

function isPartial(option) {
    if (!option.children?.length) return false;
    if (isSelected(option)) return false;
    const descendants = collectDescendants(option).slice(1);
    return descendants.some((d) => selectedIds.value.has(d.id));
}

function findAncestors(nodes, targetId, path = []) {
    for (const node of nodes) {
        if (node.id === targetId) return path;
        if (node.children?.length) {
            const found = findAncestors(node.children, targetId, [...path, node]);
            if (found) return found;
        }
    }
    return null;
}

function select(option) {
    const targets = props.selectChildren ? collectDescendants(option) : [option];
    const alreadySelected = targets.every((d) => selectedIds.value.has(d.id));

    if (alreadySelected) {
        const removeIds = new Set(targets.map((d) => d.id));
        const ancestors = findAncestors(tree.value, option.id) ?? [];
        const ancestorIds = new Set(ancestors.map((a) => a.id));
        proxyValue.value = proxyValue.value.filter((c) => !removeIds.has(c.id) && !ancestorIds.has(c.id));
    } else {
        const existingIds = selectedIds.value;
        const toAdd = targets.filter((d) => !existingIds.has(d.id));
        proxyValue.value = [...proxyValue.value, ...toAdd];
    }
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
        <div
            class="trigger-select w-100 d-flex align-items-center cursor-pointer gap-2"
            @click="showOptions = !showOptions"
        >
            <span>{{ placeholder }}</span>
            <font-awesome-icon :icon="['fas', 'chevron-down']" style="font-size: 0.65rem; opacity: 0.6" />
            <span class="ms-auto d-flex align-items-center gap-2">
                <span
                    v-if="proxyValue.length"
                    class="badge rounded-pill text-white"
                    style="background-color: var(--color-purple); font-size: 0.7rem"
                >
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
                visibility: showOptions ? 'visible' : 'hidden',
                height: showOptions ? 'auto' : '0'
            }"
            style="z-index: 1000; width: 500px"
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
                    class="option d-flex align-items-center gap-2"
                    :class="{ selected: isSelected(option) }"
                    :style="{ paddingLeft: `${0.75 + option._depth * 1}rem` }"
                    @click.stop="select(option)"
                    style="cursor: pointer"
                >
                    <span class="flex-shrink-0 option-checkbox">
                        <font-awesome-icon v-if="isSelected(option)" :icon="['fas', 'square-check']" />
                        <font-awesome-icon
                            v-else-if="isPartial(option)"
                            :icon="['fas', 'square-minus']"
                            style="opacity: 0.6"
                        />
                        <font-awesome-icon v-else :icon="['far', 'square']" />
                    </span>
                    <span
                        v-if="option.name.length > 25"
                        v-tooltip
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        :title="option.name"
                        class="flex-grow-1 truncate-1-line"
                    >
                        {{ option.name }}
                    </span>
                    <span v-else class="flex-grow-1">
                        {{ option.name }}
                    </span>
                    <span
                        v-if="option.children?.length && !searchQuery"
                        class="ms-auto flex-shrink-0 toggle-icon"
                        style="opacity: 0.5; padding: 0 0.25rem"
                        @click.stop="toggleCollapse($event, option)"
                    >
                        <font-awesome-icon
                            :icon="['fas', collapsedIds.has(option.id) ? 'chevron-down' : 'chevron-up']"
                            style="font-size: 0.65rem"
                        />
                    </span>
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

.option:hover {
    background-color: var(--color-purple-light);
}
</style>
